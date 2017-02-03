<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Webservice;

use \Dhl\Versenden\Api\Webservice\AdapterInterface;
use \Dhl\Versenden\Api\Webservice\GatewayInterface;
use \Dhl\Versenden\Api\Webservice\Request;
use \Dhl\Versenden\Api\Webservice\Response;
use \Dhl\Versenden\Webservice\Adapter\AdapterFactory;
use \Dhl\Versenden\Webservice\Response\Parser\CreateShipmentParserFactory;

/**
 * Gateway
 *
 * @category Dhl
 * @package  Dhl\Versenden\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Gateway implements GatewayInterface
{
    /**
     * @var AdapterFactory
     */
    private $apiAdapterFactory;

    /**
     * @var CreateShipmentParserFactory
     */
    private $apiResponseParserFactory;

    /**
     * Gateway constructor.
     * @param AdapterFactory $apiAdapterFactory
     * @param CreateShipmentParserFactory $apiResponseParserFactory
     */
    public function __construct(
        AdapterFactory $apiAdapterFactory,
        CreateShipmentParserFactory $apiResponseParserFactory
    ) {
        $this->apiAdapterFactory = $apiAdapterFactory;
        $this->apiResponseParserFactory = $apiResponseParserFactory;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request[] $shipmentRequests
     * @return Response\Type\CreateShipmentResponseCollection|Response\Type\CreateShipmentResponseInterface[]
     */
    public function createShipmentOrder(array $shipmentRequests)
    {
        /** @var AdapterInterface[] $apiAdapters */
        $apiAdapters = [];
        /** @var Response\Parser\CreateShipmentParserInterface[] $apiResponseParsers */
        $apiResponseParsers = [];

        $apiRequests = [];

        // divide requests by target API
        foreach ($shipmentRequests as $shipmentRequest) {
            // prepare api adapter for current shipment request
            $apiAdapter = $this->apiAdapterFactory->get($shipmentRequest->getShipperAddressCountryCode());
            if (!isset($apiAdapters[$apiAdapter->getAdapterType()])) {
                $apiAdapters[$apiAdapter->getAdapterType()] = $apiAdapter;
            }

            // prepare response parser for current shipment request
            $apiResponseParser = $this->apiResponseParserFactory->get($shipmentRequest->getShipperAddressCountryCode());
            if (!isset($apiResponseParsers[$apiAdapter->getAdapterType()])) {
                $apiResponseParsers[$apiAdapter->getAdapterType()] = $apiResponseParser;
            }

            // convert shipment request to api request, add sequence number
            //TODO(nr): implement shipment request conversion
            $apiRequests[$apiAdapter->getAdapterType()][]= $shipmentRequest;
        }


        // send request(s) to target api(s) and merge responses
        //TODO(nr): implement response handling
        $responses = [];
        foreach ($apiAdapters as $apiAdapter) {
            /** @var Request\Type\CreateShipmentRequestInterface[] $typedApiRequests */
            $typedApiRequests = $apiRequests[$apiAdapter->getAdapterType()];
            $responseParser = $apiResponseParsers[$apiAdapter->getAdapterType()];

            array_merge($responses, $apiAdapter->createShipmentOrder($typedApiRequests, $responseParser));
        }

        return $responses;
    }

    /**
     * @param string[] $shipmentNumbers
     * @return Response\Type\DeleteShipmentResponseInterface
     */
    public function deleteShipmentOrder(array $shipmentNumbers)
    {
        throw new \Exception('Not yet implemented.');
    }
}
