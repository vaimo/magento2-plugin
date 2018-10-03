<?php

namespace Yotpo\Yotpo\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Yotpo\Yotpo\Block\Config;
use Magento\Framework\Serialize\Serializer\Json;

class ApiClient
{
    const YOTPO_OAUTH_TOKEN_URL = 'https://api.yotpo.com/oauth/token';
    const YOTPO_SECURED_API_URL = 'https://api.yotpo.com';
    const YOTPO_UNSECURED_API_URL = 'http://api.yotpo.com';
    const DEFAULT_TIMEOUT = 30;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Json
     */
    protected $json;

    public function __construct(
        CurlFactory $curlFactory,
        Config $config,
        Json $json
    ) {
        $this->curlFactory = $curlFactory;
        $this->config = $config;
        $this->json = $json;
    }

    /**
     * @param $storeId
     * @return string
     * @throws LocalizedException
     */
    public function oauthAuthentication($storeId)
    {
        $appKey = $this->config->getAppKey($storeId);
        $secret = $this->config->getSecret($storeId);

        if ($appKey == null || $secret == null) {
            throw new LocalizedException(__('Missing app key or secret'));
        }

        $data = [
            'client_id' => $appKey,
            'client_secret' => $secret,
            'grant_type' => 'client_credentials'
        ];

        $result = $this->createApiPost('oauth/token', $data);
        $isValid = is_array($result['body_array']) && array_key_exists('access_token', $result['body_array']);

        if (!$isValid) {
            throw new LocalizedException(__('No access token received'));
        }

        return $result['body_array']['access_token'];
    }

    /**
     * @param $path
     * @param $data
     * @param int $timeout
     * @return array
     */
    public function createApiPost($path, $data, $timeout = self::DEFAULT_TIMEOUT)
    {
        $url = self::YOTPO_SECURED_API_URL . '/' . $path;
        $http = $this->curlFactory->create();
        $http->setConfig(['timeout' => $timeout]);
        $http->write(\Zend_Http_Client::POST, $url, '1.1', ['Content-Type: application/json'], $this->json->serialize($data));
        $response = $http->read();

        $result = [
            'code' => \Zend_Http_Response::extractCode($response),
            'body' => \Zend_Http_Response::extractBody($response),
        ];

        $result['body_array'] = $this->json->unserialize($result['body']);

        return $result;
    }

    /**
     * @param $data
     * @param $storeId
     * @return array
     * @throws LocalizedException
     */
    public function createPurchases($data, $storeId)
    {
        $appKey = $this->config->getAppKey($storeId);
        $data['utoken'] = $this->oauthAuthentication($storeId);
        $data['platform'] = 'magento';

        return $this->createApiPost('apps/' . $appKey . '/purchases', $data);
    }

    /**
     * @param $orders
     * @param $storeId
     * @return array
     * @throws LocalizedException
     */
    public function massCreatePurchases($orders, $storeId)
    {
        $appKey = $this->config->getAppKey($storeId);

        $data = [
            'utoken' => $this->oauthAuthentication($storeId),
            'platform' => 'magento',
            'orders' => $orders,
        ];

        return $this->createApiPost('apps/' . $appKey . '/purchases/mass_create', $data);
    }

    public function createApiGet($path, $timeout = self::DEFAULT_TIMEOUT)
    {
        $url = self::YOTPO_UNSECURED_API_URL . '/' . $path;
        $http = $this->curlFactory->create();
        $http->setConfig(['timeout' => $timeout]);
        $http->write(\Zend_Http_Client::GET, $url, '1.1',['Content-Type: application/json']);
        $response = $http->read();

        return [
            'code' => \Zend_Http_Response::extractCode($response),
            'body' => $this->json->unserialize(\Zend_Http_Response::extractBody($response))
        ];
    }
}
