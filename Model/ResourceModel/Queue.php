<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Yotpo\Yotpo\Model\Spi\QueueResourceInterface;
use Yotpo\Yotpo\Api\Data\QueueInterface;

class Queue extends AbstractDb implements QueueResourceInterface
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(QueueInterface::TABLE_NAME, QueueInterface::QUEUE_ID);
    }
}