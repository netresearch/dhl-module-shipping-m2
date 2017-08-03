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
 * @package   Dhl\Shipping\Test\Integration\Plugin\Order
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Test\Integration\Plugin\Order;

use Dhl\Shipping\Model\Shipping\Carrier;
use Dhl\Shipping\Plugin\Order\ShippingPlugin;
use Magento\Bundle\Helper\Data;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\TestFramework\ObjectManager;

class ShippingPluginTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ObjectManager
     */
    private $objectManager;
    /**
     * @var Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shipment;
    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    private $order;
    /**
     * @var Shipment\Track
     */
    private $track;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
        $this->shipment = $this->getMockBuilder(Shipment::class)
            ->disableOriginalConstructor()
            ->setMethods(['addTrack', 'getOrder'])
            ->getMock();
        $this->order = $this->getMockBuilder(Order::class)
                               ->disableOriginalConstructor()
                               ->setMethods(['getShippingMethod'])
                               ->getMock();
        $this->track = $this->objectManager->create(Shipment\Track::class);
    }

    public function testAroundAddTrack()
    {
        $plugin = new ShippingPlugin();

        $dhlCarrier = new DataObject(
            [
                'carrier_code' => Carrier::CODE
            ]
        );
        $fooCarrier = new DataObject(
            [
                'carrier_code' => 'foo'
            ]
        );
        $this->order->expects($this->exactly(4))
            ->method('getShippingMethod')
            ->will(
                $this->onConsecutiveCalls($fooCarrier, $dhlCarrier, $fooCarrier, $dhlCarrier)
            );
        $this->shipment->expects($this->exactly(4))
                       ->method('getOrder')
                       ->will(
                           $this->returnValue($this->order)
                       );

        $this->shipment->expects($this->exactly(3))
            ->method('addTrack')
            ->will($this->returnSelf());

        $callback = function (Shipment\Track $track) {
            $this->shipment->addTrack($track);
        };

        // test with tracking number and non-dhl carrier and dhl carrier
        $this->track->setTrackNumber('1234');

        $plugin->aroundAddTrack($this->shipment, $callback, $this->track);
        $plugin->aroundAddTrack($this->shipment, $callback, $this->track);

        // test with no-track indicator and non-dhl carrier and dhl carrier
        $this->track->setTrackNumber(Carrier::NO_TRACK);

        $plugin->aroundAddTrack($this->shipment, $callback, $this->track);
        $plugin->aroundAddTrack($this->shipment, $callback, $this->track);
    }

}
