<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Model;

use Magento\Framework\Model\AbstractModel;
use Yotpo\Yotpo\Api\Data\QueueInterface;

class Queue extends AbstractModel implements QueueInterface
{
    protected function _construct()
    {
        $this->_init('Yotpo\Yotpo\Model\ResourceModel\Queue');
        parent::_construct();
    }
}