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
 * @package   Dhl\Shipping\Utils
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Util;

use Magento\Sales\Model\Order\Shipment;

/**
 * ShipmentDetails
 *
 * @package  Dhl\Shipping\Util
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class OrderShipmentDetails
{
    /**
     * @param Shipment $orderShipment
     * @return bool
     */
    public static function isPartial(Shipment $orderShipment)
    {
        $qtyOrdered = $orderShipment->getOrder()->getTotalQtyOrdered();
        $qtyShipped = $orderShipment->getTotalQty();

        return $qtyOrdered != $qtyShipped;
    }
}
