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
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Plugin;

use \Dhl\Shipping\Webservice\Adapter\AdapterChain;
use \Dhl\Shipping\Webservice\Client\HttpClientInterface;
use \Dhl\Shipping\Webservice\Exception\ApiCommunicationException;
use \Dhl\Shipping\Webservice\Exception\ApiOperationException;
use \Dhl\Shipping\Webservice\Logger;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrderInterface;
use \Dhl\Shipping\Webservice\ResponseType\CreateShipment\LabelInterface;
use \Dhl\Shipping\Webservice\ResponseType\Generic\ItemStatusInterface;

/**
 *
 * @category Dhl
 * @package  Dhl\Shipping\Plugin
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AdapterChainPlugin
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var HttpClientInterface[]
     */
    private $httpClients = [];

    /**
     * @param Logger                $logger
     * @param HttpClientInterface[] $httpClients
     */
    public function __construct(Logger $logger, array $httpClients)
    {
        $this->logger      = $logger;
        $this->httpClients = $httpClients;
    }

    /**
     * Iterate clients, log debug
     */
    private function logDebug()
    {
        foreach ($this->httpClients as $httpClient) {
            if ($httpClient->getLastRequest()) {
                $this->logger->wsDebug($httpClient);
            }
        }
    }

    /**
     * Iterate clients, log warning
     *
     * @param ApiOperationException $e
     */
    private function logWarning(ApiOperationException $e)
    {
        foreach ($this->httpClients as $httpClient) {
            if ($httpClient->getLastRequest()) {
                $this->logger->wsWarning($httpClient, ['exception' => $e]);
            }
        }
    }

    /**
     * Iterate clients, log error
     *
     * @param ApiCommunicationException $e
     */
    private function logError(ApiCommunicationException $e)
    {
        foreach ($this->httpClients as $httpClient) {
            if ($httpClient->getLastRequest()) {
                $this->logger->wsError($httpClient, ['exception' => $e]);
            }
        }
    }

    /**
     * Will be called the moment the Gl adapter calls the HTTP Client to create a shipment and label
     *
     * @param AdapterChain             $subject
     * @param callable                 $proceed
     * @param ShipmentOrderInterface[] $shipmentOrders
     *
     * @return LabelInterface[]
     * @throws ApiOperationException
     * @throws ApiCommunicationException
     */
    public function aroundCreateLabels(AdapterChain $subject, callable $proceed, array $shipmentOrders)
    {
        try {
            $labels = $proceed($shipmentOrders);
            $this->logDebug();
        } catch (ApiOperationException $e) {
            $this->logWarning($e);
            throw $e;
        } catch (ApiCommunicationException $e) {
            $this->logError($e);
            throw $e;
        }

        return $labels;
    }

    /**
     * Will be called the moment the Gl adapter calls the HTTP Client to remove a shipment
     *
     * @param AdapterChain $subject
     * @param callable $proceed
     * @param string[] $shipmentNumbers
     *
     * @return ItemStatusInterface[]
     * @throws ApiOperationException
     * @throws ApiCommunicationException
     */
    public function aroundCancelLabels(AdapterChain $subject, callable $proceed, array $shipmentNumbers)
    {
        try {
            $status = $proceed($shipmentNumbers);
            $this->logDebug();
        } catch (ApiOperationException $e) {
            $this->logWarning($e);
            throw $e;
        } catch (ApiCommunicationException $e) {
            $this->logError($e);
            throw $e;
        }

        return $status;
    }
}
