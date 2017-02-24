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

use \Dhl\Shipping\Api\Data\Webservice\RequestType;
use \Dhl\Shipping\Api\Webservice\RequestMapper\GlDataMapperInterface;

/**
 * GlDataMapper
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 *
 * @SuppressWarnings(MEQP2.Classes.ObjectInstantiation)
 */
class GlDataMapper implements GlDataMapperInterface
{
    /**
     * Create api specific request object from framework standardized object.
     *
     * @param \Dhl\Shipping\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrderInterface $shipmentOrder
     * @return object The "BCS shipment order" or "GL API shipment" entity
     */
    public function mapShipmentOrder(RequestType\CreateShipment\ShipmentOrderInterface $shipmentOrder)
    {
        // TODO: Implement mapShipmentOrder() method.
    }

    /**
     * Create api specific request object from framework standardized object.
     *
     * @param \Dhl\Shipping\Api\Data\Webservice\RequestType\GetTokenRequestInterface $request
     * @return object
     */
    public function mapGetTokenRequest(RequestType\GetTokenRequestInterface $request)
    {
        // TODO: Implement mapGetTokenRequest() method.
    }
}
