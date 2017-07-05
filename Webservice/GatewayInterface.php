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

use \Dhl\Shipping\Webservice\ResponseType;
use \Dhl\Shipping\Webservice\ResponseType\CreateShipmentResponseCollection;
use \Dhl\Shipping\Webservice\ResponseType\DeleteShipmentResponseCollection;

/**
 * GatewayInterface
 *
 * The webservice gateway is the central entry point for all API operations. It
 * can be used from within the carrier model, grid mass actions, or cron tasks.
 *
 * @api
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface GatewayInterface
{
    /**
     * @param \Magento\Shipping\Model\Shipment\Request[] $shipmentRequests
     * @return ResponseType\CreateShipmentResponseInterface|ResponseType\CreateShipment\LabelInterface[]
     */
    public function createLabels(array $shipmentRequests);

    /**
     * @param string[] $shipmentNumbers
     * @return ResponseType\DeleteShipmentResponseInterface|ResponseType\Generic\ItemStatusInterface[]
     */
    public function cancelLabels(array $shipmentNumbers);
}
