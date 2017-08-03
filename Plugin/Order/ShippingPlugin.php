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
 * @package   Dhl\Shipping\Plugin\Order
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Plugin\Order;

use Dhl\Shipping\Model\Shipping\Carrier;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Track;

class ShippingPlugin
{

    /**
     * @param Shipment $shipment
     * @param callable $proceed
     * @param Track $track
     * @see Shipment::addTrack()
     * @return Shipment
     */
    public function aroundAddTrack(Shipment $shipment, callable $proceed, Track $track)
    {
        $carrierCode = $shipment->getOrder()->getShippingMethod(true)->getCarrierCode();
        if ($carrierCode === Carrier::CODE && $track->getTrackNumber() === Carrier::NO_TRACK) {
            return $shipment;
        }

        return $proceed($track);
    }

}