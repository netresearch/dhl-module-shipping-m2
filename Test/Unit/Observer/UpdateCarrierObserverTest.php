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
 * @package   Dhl\Versenden\Test\Unit
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Observer;

use \Dhl\Versenden\Api\ConfigInterface;
use \Dhl\Versenden\Model\Config;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\DataObject;
use Magento\Framework\Event;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * UpdateCarrierObserverTest
 *
 * @category Dhl
 * @package  Dhl\Versenden\Test\Unit
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UpdateCarrierObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigInterface|MockObject
     */
    private $config;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);

        $this->config = $this->getMock(Config::class, ['canProcessMethod'], [], '', false);
    }

    /**
     * @test
     */
    public function shippingMethodCannotBeProcessed()
    {
        $fooMethod = 'foo_bar';
        $dhlMethod = 'dhlversenden_bar';

        /** @var UpdateCarrierObserver $carrierObserver */
        $carrierObserver = $this->objectManager->getObject(UpdateCarrierObserver::class, [
            'config' => $this->config,
        ]);

        $order = new DataObject(['shipping_method' => $fooMethod]);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMock(Observer::class, [], [], '', false);
        /** @var Event|MockObject $eventMock */
        $eventMock = $this->getMock(Event::class, ['getData'], [], '', false);
        $eventMock->expects($this->once())
            ->method('getData')
            ->with('order', null)
            ->willReturn($order);
        $observerMock->expects($this->exactly(1))->method('getEvent')->willReturn($eventMock);

        $this->config
            ->expects($this->once())
            ->method('canProcessMethod')
            ->willReturn(false);

        $carrierObserver->execute($observerMock);

        $this->assertSame($fooMethod, $order->getData('shipping_method'));
        $this->assertNotSame($dhlMethod, $order->getData('shipping_method'));
    }

    /**
     * @test
     */
    public function shippingMethodCanBeProcessed()
    {
        $fooMethod = 'foo_bar';
        $dhlMethod = 'dhlversenden_bar';

        /** @var UpdateCarrierObserver $carrierObserver */
        $carrierObserver = $this->objectManager->getObject(UpdateCarrierObserver::class, [
            'config' => $this->config,
        ]);

        $order = new DataObject(['shipping_method' => $fooMethod]);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMock(Observer::class, [], [], '', false);
        $eventMock = $this->getMock(Event::class, ['getData'], [], '', false);
        $eventMock->expects($this->once())
            ->method('getData')
            ->with('order', null)
            ->willReturn($order);
        $observerMock->expects($this->exactly(1))->method('getEvent')->willReturn($eventMock);

        $this->config
            ->expects($this->once())
            ->method('canProcessMethod')
            ->willReturn(true);

        $carrierObserver->execute($observerMock);

        $this->assertSame($dhlMethod, $order->getData('shipping_method'));
        $this->assertNotSame($fooMethod, $order->getData('shipping_method'));
    }
}
