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

use \Dhl\Versenden\Api\Webservice\GatewayInterface;

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
     * Gateway constructor.
     * @param AdapterFactory $apiAdapterFactory
     */
    public function __construct(AdapterFactory $apiAdapterFactory)
    {
        $this->apiAdapterFactory = $apiAdapterFactory;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request[] $shipmentRequests
     * @return \Dhl\Versenden\Api\Webservice\Response\Type\CreateShipmentResponseInterface
     */
    public function createShipmentOrder(array $shipmentRequests)
    {
        $shipmentRequest = $shipmentRequests[0];

        $apiAdapter = $this->apiAdapterFactory->create(['data' => [
            'shipperCountryISOCode' => $shipmentRequest->getShipperAddressCountryCode(),
        ]]);

        //TODO(nr): implement
        return $apiAdapter->createShipmentOrder(null, null);
    }

    /**
     * @param string[] $shipmentNumbers
     * @return \Dhl\Versenden\Api\Webservice\Response\Type\DeleteShipmentResponseInterface
     */
    public function deleteShipmentOrder(array $shipmentNumbers)
    {
        throw new \Exception('Not yet implemented.');
    }
}
