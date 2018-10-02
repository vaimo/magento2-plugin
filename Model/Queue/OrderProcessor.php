<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */

namespace Yotpo\Yotpo\Model\Queue;

use Magento\Braintree\Model\LocaleResolver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Escaper;
use Magento\Store\Model\App\Emulation;
use Yotpo\Yotpo\Helper\ApiClient;
use Magento\Framework\UrlInterface;

class OrderProcessor
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var ImageHelper;
     */
    private $imageHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var ApiClient
     */
    private $apiClient;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ImageHelper $imageHelper,
        Escaper $escaper,
        Emulation $emulation,
        ApiClient $apiClient
    ) {
        $this->orderRepository = $orderRepository;
        $this->imageHelper = $imageHelper;
        $this->escaper = $escaper;
        $this->emulation = $emulation;
        $this->apiClient = $apiClient;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getOrderData($order)
    {
        $result = [
            'email' => $order->getCustomerEmail(),
            'customer_name' => $order->getCustomerName(),
            'order_id' => $order->getIncrementId(),
            'platform' => 'magento',
            'currency_iso' => $order->getOrderCurrency()->getCode(),
            'order_date' => $order->getCreatedAt(),
            'products' => $this->getProductData($order),
        ];

        return $result;

    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getProductData($order)
    {
        $result = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            if (!$product = $item->getProduct()) {
                continue;
            }

            $specs = ['external_sku' => $product->getSku()];

            if ($product->getUpc()) {
                $specs['upc'] = $product->getUpc();
            }

            if ($product->getIsbn()) {
                $specs['isbn'] = $product->getIsbn();
            }

            if ($product->getBrand()) {
                $specs['brand'] = $product->getBrand();
            }

            if ($product->getMpn()) {
                $specs['mpn'] = $product->getMpn();
            }

            $result[$product->getId()] = [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'url' => $product->getUrlInStore(['_store' => $order->getStoreId()]),
                'image' => $order->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage(),
                'description' => $this->escaper->escapeHtml($product->getDescription()),
                'price' => $item->getPrice(),
                'specs' => $specs,
            ];
        }

        return $result;
    }

    /**
     * @param $id
     * @throws LocalizedException
     */
    public function execute($id)
    {
        $order = $this->orderRepository->get($id);
        $storeId = $order->getStoreId();

        try {
            $this->emulation->startEnvironmentEmulation($storeId, 'frontend', true);
            $data = $this->getOrderData($order);
            $data['utoken'] = $this->apiClient->oauthAuthentication($storeId);

            if ($data['utoken'] == null) {
                throw new LocalizedException(__('Access token received from Yotpo API is null'));
            }

            $this->apiClient->createPurchases($data, $storeId);
        } finally {
            $this->emulation->stopEnvironmentEmulation();
        }
    }
}