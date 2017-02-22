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

use \Dhl\Versenden\Api\Webservice\Adapter\AdapterChainInterface;
use \Dhl\Versenden\Api\Webservice\GatewayInterface;
use \Dhl\Versenden\Api\Webservice\RequestMapper;
use \Dhl\Versenden\Api\Data\Webservice\RequestType;
use \Dhl\Versenden\Api\Data\Webservice\ResponseType;
use \Dhl\Versenden\Webservice\ResponseType\CreateShipmentResponseCollection;

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
     * @var AdapterChainInterface
     */
    private $apiAdapters;

    /**
     * @var RequestMapper\AppDataMapperInterface
     */
    private $appDataMapper;

    /**
     * Gateway constructor.
     * @param AdapterChainInterface $apiAdapters
     * @param RequestMapper\AppDataMapperInterface $dataMapper
     */
    public function __construct(
        AdapterChainInterface $apiAdapters,
        RequestMapper\AppDataMapperInterface $dataMapper
    ) {
        $this->apiAdapters = $apiAdapters;
        $this->appDataMapper = $dataMapper;
    }

    /**
     * @api
     * @param \Magento\Shipping\Model\Shipment\Request[] $shipmentRequests
     * @return CreateShipmentResponseCollection|ResponseType\CreateShipmentResponseInterface[]
     */
    public function createLabels(array $shipmentRequests)
    {
        /** @var RequestType\CreateShipment\ShipmentOrderInterface[] $shipmentOrders */
        $shipmentOrders = [];

        // convert M2 shipment request to api request, add sequence number
        foreach ($shipmentRequests as $sequenceNumber => $request) {
            $shipmentOrders[] = $this->appDataMapper->mapShipmentRequest($request, $sequenceNumber);
        }

        // send shipment orders to APIs
        $response = $this->apiAdapters->createLabels($shipmentOrders);
        return $response;
    }

    /**
     * @api
     * @param string[] $shipmentNumbers
     * @return ResponseType\DeleteShipmentResponseInterface
     */
    public function cancelLabels(array $shipmentNumbers)
    {
        // send shipment orders to APIs
        $response = $this->apiAdapters->cancelLabels($shipmentNumbers);
        return $response;
    }
}
