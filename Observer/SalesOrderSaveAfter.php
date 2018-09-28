<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Yotpo\Yotpo\Api\Data\QueueInterfaceFactory;
use Yotpo\Yotpo\Api\QueueRepositoryInterface;
use Magento\Sales\Model\Order;
use Yotpo\Yotpo\Api\Data\QueueInterface;

class SalesOrderSaveAfter implements ObserverInterface
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

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        if ($order = $observer->getEvent()->getOrder()) {
            if (true || $order->getState() == Order::STATE_COMPLETE) {
                $queue = $this->queueFactory->create();
                $queue->setStatus(QueueInterface::STATUS_QUEUED)
                    ->setEntityType('order')
                    ->setEntityId($order->getId());

                $this->queueRepository->save($queue);
            }
        }
    }
}