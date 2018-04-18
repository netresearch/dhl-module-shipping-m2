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
 * @category  Dhl
 * @package   Dhl\Shipping\Cron\AutoCreate
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\AutoCreate;

use Magento\Framework\Api\SimpleBuilderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Shipping\Model\Shipment\Request;

/**
 * Interface RequestBuilderInterface
 *
 * @package Dhl\Shipping\AutoCreate
 */
interface RequestBuilderInterface extends SimpleBuilderInterface
{
    /**
     * Adds all relevant data from the orders shipment to the request
     *
     * @param ShipmentInterface $orderShipment
     * @return $this
     */
    public function setOrderShipment(ShipmentInterface $orderShipment);

    /**
     * Retrieve filled object
     *
     * @return Request
     */
    public function create();
}
