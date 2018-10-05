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
use Yotpo\Yotpo\Helper\ApiClient;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Yotpo\Yotpo\Model\Queue\OrderProcessor;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Sales\Model\Order;

class MassCreatePurchases extends Command
{
    const BATCH_SIZE = 200;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var OrderProcessor
     */
    private $orderProcessor;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        State $state,
        ApiClient $apiClient,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        OrderProcessor $orderProcessor,
        Emulation $emulation,
        StoreManagerInterface $storeManager,
        $name = null
    ) {
        parent::__construct($name);

        $this->state = $state;
        $this->apiClient = $apiClient;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->orderProcessor = $orderProcessor;
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('yotpo:mass:create:purchases')
            ->setDescription('Mass Create Purchases to Yotpo')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Created From Date')
            ->addOption('store', null, InputOption::VALUE_OPTIONAL, 'Store Id');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param \Magento\Store\Model\Store $store
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processStore(InputInterface $input, OutputInterface $output, $store)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('store_id', $store->getId());
        $collection->addFieldToFilter('state', Order::STATE_COMPLETE);

        if ($createdFrom = $input->getOption('from')) {
            $collection->addFieldToFilter('created_at', ['gteq' => $createdFrom]);
        }

        if ($size = $collection->getSize()) {
            $output->write('Creating ' . $size . ' orders for store ' . $store->getCode());
            $pageCount = ceil($collection->getSize() / self::BATCH_SIZE);

            try {
                $this->emulation->startEnvironmentEmulation($store->getId(), Area::AREA_FRONTEND, true);

                for ($page = 1; $page <= $pageCount; $page++) {
                    $orders = [];
                    $collection->clear()->setPage($page, self::BATCH_SIZE);

                    foreach ($collection as $order) {
                        $data = $this->orderProcessor->getOrderData($order);

                        if (!empty($data['products'])) {
                            $orders[] = $data;
                        }
                    }

                    if ($orders) {
                        $result = $this->apiClient->massCreatePurchases($orders, $store->getId());

                        if ($result['code'] != 200) {
                            $output->writeln($result['body']);
                        } else {
                            $output->write('.');
                        }
                    }
                };

                $output->writeln(' <info>[ DONE ]</info>');
            } catch (\Exception $e) {
                $output->writeln(' <error>' . $e->getMessage() . '</error>');
            } finally {
                $this->emulation->stopEnvironmentEmulation();
            }
        }
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

        if ($storeId = $input->getOption('store')) {
            $store = $this->storeManager->getStore($storeId);
            $this->processStore($input, $output, $store);
        } else {
            foreach ($this->storeManager->getStores() as $store) {
                $this->processStore($input, $output, $store);
            }
        }

        $output->writeln('<info>COMPLETE</info>');
    }
}
