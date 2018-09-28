<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface QueueSearchResultInterface extends SearchResultsInterface
{
    public function getItems();

    public function setItems(array $items);
}
