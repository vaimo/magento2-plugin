<?php

namespace Yotpo\Yotpo\Model;

class Richsnippet extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Yotpo\Yotpo\Model\ResourceModel\Richsnippet');
    }

    public function isValid()
    {
        $expirationTime = strtotime($this->getExpirationTime());
        return ($expirationTime > time());
    }

    public function getSnippetByProductIdAndStoreId($product_id, $store_id)
    {
        $col = $this->getCollection()
            ->addFieldToFilter('store_id', $store_id)
            ->addFieldToFilter('product_id', $product_id)
            ->setPageSize(1);
        $snippet = $col->getFirstItem();

        if (!$snippet->getId()) {
            return null;
        }
        return $snippet;
    }
}