<?php
/**
 * Dhl Shipping
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * PHP version 7
 *
 * @category  Dhl
 * @package   Dhl\Shipping\Api
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Webservice\Client;

use \Dhl\Shipping\Api\Config\GlConfigInterface;
use Dhl\Shipping\Api\Webservice\Client\GlRestClientInterface;

/**
 * Business Customer Shipping API SOAP client
 *
 * @category Dhl
 * @package  Dhl\Shipping\Api
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class GlRestClient implements GlRestClientInterface
{
    /**
     * @var GlConfigInterface
     */
    private $config;

    /**
     * @var \Zend\Http\ClientFactory
     */
    private $zendClientFactory;

    /**
     * @var \Zend\Http\Client
     */
    private $zendClient;

    /**
     * GlRestClient constructor.
     * @param GlConfigInterface $config
     * @param \Zend\Http\ClientFactory $zendClientFactory
     */
    public function __construct(
        GlConfigInterface $config,
        \Zend\Http\ClientFactory $zendClientFactory
    ) {
        $this->config = $config;
        $this->zendClientFactory = $zendClientFactory;
    }

    /**
     * Requests new tokens.
     *
     * @return string
     */
    public function authenticate()
    {
        $state = md5(time());
        $this->zendClient = $this->zendClientFactory->create();
        $this->zendClient->setMethod(\Zend\Http\Request::METHOD_GET);
        $this->zendClient->setParameterGet([
            'username' => $this->config->getAuthUsername(),
            'password' => $this->config->getAuthPassword(),
            'state' => $state,
        ]);

        try {
            $this->zendClient->send();

            $response = $this->zendClient->getResponse();
            if ($response->isSuccess()) {
                $responseType = json_decode($response->getBody(), true);
                if ($state !== $responseType['state']) {
                    //TODO(nr): throw exception
                }
                $this->config->saveAuthToken($responseType['access_token']);
                return $this->zendClient->getLastRawResponse();
            }

            //TODO(nr): throw exception
        } catch (\Zend\Http\Exception\RuntimeException $runtimeException) {
            //TODO(nr): throw exception
        }
    }

    /**
     * Creates shipments.
     *
     * @param string $rawRequest
     * @return string
     */
    public function generateLabels($rawRequest)
    {
        $this->zendClient = $this->zendClientFactory->create();
        $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST);
        $this->zendClient->setHeaders([
            'Authorization' => 'Bearer ' . $this->config->getAuthToken(),
            'X-CorrelationID' => hash('crc32', $rawRequest),
        ]);
        $this->zendClient->setParameterGet([
            'format' => 'PDF',
            'labelSize' => $this->config->getLabelSize(),
            'pageSize' => $this->config->getPageSize(),
            'layout' => $this->config->getPageLayout(),
        ]);
        $this->zendClient->setRawBody($rawRequest);

        try {
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();

            // success, return json response
            if ($response->isSuccess()) {
                return $response->getBody();
            }

            // http status error
            if ($response->getStatusCode() === \Zend\Http\Response::STATUS_CODE_403
                && $response->getBody() === 'INVALID_TOKEN'
            ) {
                $this->authenticate();
                return $this->generateLabels($rawRequest);
            }

            //TODO(nr): throw exception
        } catch (\Zend\Http\Exception\RuntimeException $runtimeException) {
            //TODO(nr): throw exception
        }
    }

    /**
     * @return string
     */
    public function getLastRequest()
    {
        return $this->zendClient->getLastRawRequest();
    }

    /**
     * @return string
     */
    public function getLastRequestHeaders()
    {
        return $this->zendClient->getRequest()->getHeaders()->toString();
    }

    /**
     * @return string
     */
    public function getLastResponse()
    {
        return $this->zendClient->getLastRawResponse();
    }

    /**
     * @return string
     */
    public function getLastResponseHeaders()
    {
        return $this->zendClient->getResponse()->getHeaders()->toString();
    }
}
