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

use \Dhl\Versenden\Api\Webservice\Adapter\AdapterInterface;
use \Dhl\Versenden\Api\Webservice\GatewayInterface;
use \Dhl\Versenden\Api\Webservice\Request;
use \Dhl\Versenden\Api\Webservice\Response;
use \Dhl\Versenden\Api\Data\Webservice\Request as RequestData;
use \Dhl\Versenden\Api\Data\Webservice\Response as ResponseData;
use \Dhl\Versenden\Webservice\Adapter\AdapterFactory;
use \Dhl\Versenden\Webservice\Response\Type\CreateShipmentResponseCollection;

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
     * @var Request\Mapper\AppRequestMapperInterface
     */
    private $requestMapper;

    /**
     * Gateway constructor.
     * @param AdapterFactory $apiAdapterFactory
     * @param Request\Mapper\AppRequestMapperInterface $requestMapper
     */
    public function __construct(
        AdapterFactory $apiAdapterFactory,
        Request\Mapper\AppRequestMapperInterface $requestMapper
    ) {
        $this->apiAdapterFactory = $apiAdapterFactory;
        $this->requestMapper = $requestMapper;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request[] $shipmentRequests
     * @return CreateShipmentResponseCollection|ResponseData\Type\CreateShipmentResponseInterface[]
     */
    public function createShipmentOrder(array $shipmentRequests)
    {
        /** @var AdapterInterface[] $apiAdapters */
        $apiAdapters = [];
        /** @var RequestData\Type\CreateShipmentRequestInterface[][] $apiRequests */
        $apiRequests = [];
        /** @var ResponseData\Type\CreateShipmentResponseInterface[] $apiResponses */
        $apiResponses = [];

        // divide requests by target API
        foreach ($shipmentRequests as $sequenceNumber => $shipmentRequest) {
            $apiType = $this->apiAdapterFactory->getAdapterType($shipmentRequest->getShipperAddressCountryCode());

            // prepare api adapter for current shipment request
            $apiAdapters[$apiType] = $this->apiAdapterFactory->get($apiType);

            // convert M2 shipment request to api request, add sequence number
            $apiRequests[$apiType][$sequenceNumber] = $this->requestMapper->mapShipmentRequest($shipmentRequest);
        }

        // send request(s) to target api(s) and merge responses
        //TODO(nr): implement response handling
        foreach ($apiAdapters as $apiType => $apiAdapter) {
            $apiTypeResponses = $apiAdapter->createShipmentOrder($apiRequests[$apiType]);
            $apiResponses = array_merge($apiResponses, $apiTypeResponses);
        }

        return $apiResponses;
    }

    /**
     * @param string[] $shipmentNumbers
     * @return ResponseData\Type\DeleteShipmentResponseInterface
     */
    public function deleteShipmentOrder(array $shipmentNumbers)
    {
        throw new \Exception('Not yet implemented.');
    }
}
