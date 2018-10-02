<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Cron;

use Yotpo\Yotpo\Api\Data\QueueInterfaceFactory;
use Yotpo\Yotpo\Api\QueueRepositoryInterface;

class Queue
{
    /**
     * @var QueueInterfaceFactory
     */
    private $queueFactory;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    public function __construct(
        QueueInterfaceFactory $queueFactory,
        QueueRepositoryInterface $queueRepository
    ) {
        $this->queueFactory = $queueFactory;
        $this->queueRepository = $queueRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Yotpo\Yotpo\Model\Queue $queue */
        foreach ($this->queueRepository->getListQueued() as $queue) {
            $queue->process();
            $this->queueRepository->save($queue);
        }
    }
}