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
 * @package   Dhl\Shipping
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Webservice\Client;

use \Dhl\Shipping\Config\GlConfigInterface;
use \Dhl\Shipping\Webservice\Client\GlRestClientInterface;
use \Dhl\Shipping\Util\Version;
use \Dhl\Shipping\Webservice\Exception\GlOperationException;
use \Dhl\Shipping\Webservice\Exception\GlAuthorizationException;
use \Dhl\Shipping\Webservice\Exception\GlCommunicationException;

/**
 * Global Label API REST client
 *
 * @category Dhl
 * @package  Dhl\Shipping
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
     * @var Version
     */
    private $version;

    /**
     * @var \Zend\Http\Client
     */
    private $zendClient;

    /**
     * GlRestClient constructor.
     *
     * @param GlConfigInterface $config
     * @param Version           $version
     * @param \Zend\Http\Client $zendClient
     */
    public function __construct(
        GlConfigInterface $config,
        Version $version,
        \Zend\Http\Client $zendClient
    ) {
        $this->config     = $config;
        $this->version    = $version;
        $this->zendClient = $zendClient;
    }

    /**
     * Request new access token and save to config.
     *
     * @throws GlAuthorizationException
     * @throws GlCommunicationException
     */
    public function authenticate()
    {
        try {
            $this->zendClient->reset();
            $this->zendClient->setUri($this->config->getApiEndpoint() . 'v1/auth/accesstoken');
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_GET);
            $this->zendClient->setAuth($this->config->getAuthUsername(), $this->config->getAuthPassword());
            $this->zendClient->setOptions([
                'trace' => 1,
                'maxredirects' => 0,
                'timeout' => 30,
                'useragent' => $this->version->getFullVersion('Magento/%1$s DHL-plug-in/%2$s'),
            ]);
        } catch (\Zend\Http\Exception\InvalidArgumentException $argumentException) {
            throw GlCommunicationException::setup($argumentException->getMessage());
        }

        try {
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();

            if ($response->getStatusCode() === \Zend\Http\Response::STATUS_CODE_401) {
                throw GlAuthorizationException::create($response->getBody());
            }

            $responseType = json_decode($response->getBody(), true);
            $this->config->saveAuthToken($responseType['access_token']);
        } catch (\Zend\Http\Exception\RuntimeException $runtimeException) {
            throw GlCommunicationException::runtime($runtimeException->getMessage());
        }
    }

    /**
     * Creates shipments.
     *
     * @param string $rawRequest
     * @return \Zend\Http\Response
     * @throws GlOperationException
     * @throws GlCommunicationException
     */
    public function generateLabels($rawRequest)
    {
        try {
            $this->zendClient->reset();
            $this->zendClient->setUri($this->config->getApiEndpoint() . 'shipping/v1/label');
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_POST);

            $this->zendClient->setOptions([
                'trace' => 1,
                'maxredirects' => 0,
                'timeout' => 30,
                'useragent' => $this->version->getFullVersion('Magento/%1$s DHL-plug-in/%2$s'),
            ]);
            $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->config->getAuthToken(),
                'X-CorrelationID' => hash('crc32', $rawRequest),
            ]);
            $this->zendClient->setParameterGet([
                'format' => 'PDF',
                'labelSize' => $this->config->getLabelSize(),
                'pageSize' => $this->config->getPageSize(),
                'layout' => $this->config->getPageLayout(),
                'autoClose' => '1',
            ]);
            $this->zendClient->setRawBody($rawRequest);
        } catch (\Zend\Http\Exception\InvalidArgumentException $argumentException) {
            throw GlCommunicationException::setup($argumentException->getMessage());
        }

        try {
            $this->zendClient->send();
            $response = $this->zendClient->getResponse();
        } catch (\Zend\Http\Exception\RuntimeException $runtimeException) {
            throw GlCommunicationException::runtime($runtimeException->getMessage());
        }

        // Unauthorized, request token and retry
        if ($response->getStatusCode() === \Zend\Http\Response::STATUS_CODE_401) {
            $this->authenticate();
            return $this->generateLabels($rawRequest);
        }

        // error responses with json body: 400 (Bad Request), 429 (Too many requests), or 503 (Service Unavailable)
        $errorCodes = [
            \Zend\Http\Response::STATUS_CODE_400,
            \Zend\Http\Response::STATUS_CODE_429,
            \Zend\Http\Response::STATUS_CODE_503,
        ];
        if (in_array($response->getStatusCode(), $errorCodes)) {
            throw GlOperationException::create($response->getBody());
        }

        // unknown error response codes
        if (!$response->isSuccess()) {
            throw new GlCommunicationException($response->getBody());
        }

        return $response;
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
