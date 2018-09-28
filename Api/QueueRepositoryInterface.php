<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Yotpo\Yotpo\Api\Data\QueueInterface;

interface QueueRepositoryInterface
{
    public function getList(SearchCriteriaInterface $searchCriteria);
    public function getListQueued($entityType = '', $pageSize = 100);
    public function save(QueueInterface $entity);
}