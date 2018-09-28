<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Yotpo\Yotpo\Api\Data\QueueInterface;
use Yotpo\Yotpo\Api\Data\QueueSearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class Collection extends AbstractCollection implements QueueSearchResultInterface
{
    protected $_idFieldName = QueueInterface::QUEUE_ID;

    protected function _construct()
    {
        $this->_init(\Yotpo\Yotpo\Model\Queue::class, \Yotpo\Yotpo\Model\ResourceModel\Queue::class);
    }

    /**
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}