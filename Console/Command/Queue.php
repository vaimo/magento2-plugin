<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\State;
use Yotpo\Yotpo\Api\Data\QueueInterfaceFactory;
use Yotpo\Yotpo\Api\QueueRepositoryInterface;
use Yotpo\Yotpo\Api\Data\QueueInterface;
use Yotpo\Yotpo\Helper\ApiClient;
use Magento\Sales\Api\OrderRepositoryInterface;

class Queue extends Command
{
    /**
     * @var State
     */
    protected $state;

    /**
     * @var QueueInterfaceFactory
     */
    private $queueFactory;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        State $state,
        QueueInterfaceFactory $queueFactory,
        QueueRepositoryInterface $queueRepository,
        ApiClient $apiClient,
        OrderRepositoryInterface $orderRepository,
        $name = null
    )
    {
        parent::__construct($name);

        $this->state = $state;
        $this->queueFactory = $queueFactory;
        $this->queueRepository = $queueRepository;
        $this->apiClient = $apiClient;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('yotpo:queue')
            ->setDescription('Process Yotpo Queue')
            ->addOption('entity_type', null, InputOption::VALUE_OPTIONAL, 'Entity Type', '')
            ->addOption('limit', null, InputOption::VALUE_OPTIONAL, 'Limit', 100);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $entityType = $input->getOption('entity_type');
        $limit = $input->getOption('limit');

        foreach ($this->queueRepository->getListQueued($entityType, $limit) as $queue) {
            try {
                $output->write(sprintf('%s/%d ', $queue->getEntityType(), $queue->getEntityId()));

                $order = $this->orderRepository->get($queue->getEntityId());
                $data = $this->apiClient->prepareOrderData($order);
                $queue->setStatus(QueueInterface::STATUS_EXPORTED);
                $output->writeln("<info>[ OK ]</info>");
            } catch (\Exception $e) {
                $queue->setStatus(QueueInterface::STATUS_FAILED);
                $queue->setMessage($e->getMessage());
                $output->writeln("<error>[ ERROR ]</error>");
            }

            $this->queueRepository->save($queue);
        }

        $output->writeln("<info>DONE</info>");
    }
}
