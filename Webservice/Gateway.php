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
 * @package   Dhl\Shipping\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Webservice;

use \Dhl\Shipping\Webservice\GatewayInterface;
use \Dhl\Shipping\Webservice\RequestMapper;
use \Dhl\Shipping\Webservice\RequestType;
use \Dhl\Shipping\Webservice\ResponseType;
use \Dhl\Shipping\Webservice\Adapter\AdapterChain;
use \Dhl\Shipping\Webservice\Exception\ApiAdapterException;
use \Dhl\Shipping\Webservice\Exception\CreateShipmentValidationException;
use \Dhl\Shipping\Webservice\ResponseType\CreateShipmentResponseCollection;
use \Dhl\Shipping\Webservice\ResponseType\DeleteShipmentResponseCollection;

/**
 * Gateway
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Gateway implements GatewayInterface
{
    /**
     * @var AdapterChain
     */
    private $apiAdapters;

    /**
     * @var RequestMapper\AppDataMapperInterface
     */
    private $appDataMapper;

    /**
     * Gateway constructor.
     * @param AdapterChain $apiAdapters
     * @param RequestMapper\AppDataMapperInterface $dataMapper
     */
    public function __construct(
        AdapterChain $apiAdapters,
        RequestMapper\AppDataMapperInterface $dataMapper
    ) {
        $this->apiAdapters = $apiAdapters;
        $this->appDataMapper = $dataMapper;
    }

    /**
     * @api
     * @param \Magento\Shipping\Model\Shipment\Request[] $shipmentRequests
     * @return ResponseType\CreateShipmentResponseInterface|ResponseType\CreateShipment\LabelInterface[]
     */
    public function createLabels(array $shipmentRequests)
    {
        /** @var RequestType\CreateShipment\ShipmentOrderInterface[] $shipmentOrders */
        $shipmentOrders = [];
        $invalidRequests = [];

        // convert M2 shipment request to api request, add sequence number
        foreach ($shipmentRequests as $sequenceNumber => $request) {
            try {
                $shipmentOrders[] = $this->appDataMapper->mapShipmentRequest($request, $sequenceNumber);
            } catch (CreateShipmentValidationException $e) {
                $invalidRequests[$sequenceNumber] = $e->getMessage();
            }
        }

        // send shipment orders to APIs
        try {
            $labels = $this->apiAdapters->createLabels($shipmentOrders);
            $response = CreateShipmentResponseCollection::fromResponse($labels, $invalidRequests);
        } catch (ApiAdapterException $exception) {
            $response = CreateShipmentResponseCollection::fromError($exception, $invalidRequests);
        }

        return $response;
    }

    /**
     * @api
     * @param string[] $shipmentNumbers
     * @return ResponseType\DeleteShipmentResponseInterface|ResponseType\Generic\ItemStatusInterface[]
     */
    public function cancelLabels(array $shipmentNumbers)
    {
        if (empty($shipmentNumbers)) {
            $response = DeleteShipmentResponseCollection::fromResponse([]);
            return $response;
        }

        // send shipment cancellation requests to APIs
        try {
            $items = $this->apiAdapters->cancelLabels($shipmentNumbers);
            $response = DeleteShipmentResponseCollection::fromResponse($items);
        } catch (ApiAdapterException $exception) {
            $response = DeleteShipmentResponseCollection::fromError($exception);
        }

        return $response;
    }
}
