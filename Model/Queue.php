<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Model;

use Magento\Framework\Model\AbstractModel;
use Yotpo\Yotpo\Api\Data\QueueInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Yotpo\Yotpo\Model\Queue\OrderProcessor;
use Magento\Framework\Exception\LocalizedException;

class Queue extends AbstractModel implements QueueInterface
{
    /**
     * @var OrderProcessor;
     */
    private $orderProcessor;

    protected function _construct()
    {
        $this->_init('Yotpo\Yotpo\Model\ResourceModel\Queue');
        parent::_construct();
    }

    public function __construct(
        Context $context,
        Registry $registry,
        OrderProcessor $orderProcessor,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->orderProcessor = $orderProcessor;
    }


    public function process()
    {
        try {
            switch ($this->getEntityType()) {
                case 'order':
                    $this->orderProcessor->execute($this->getEntityId());
                    break;
                default:
                    throw new LocalizedException(__('Invalid entity type: %1', $this->getEntityType()));
            }

            $this->setStatus(QueueInterface::STATUS_PROCESSED);
            $this->setMessage('OK');
        } catch (\Exception $e) {
            $this->setStatus(QueueInterface::STATUS_FAILED);
            $this->setMessage($e->getMessage());
        }
    }
}