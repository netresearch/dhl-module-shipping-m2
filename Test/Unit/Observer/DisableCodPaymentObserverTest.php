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
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Observer;

use \Dhl\Versenden\Api\ConfigInterface;
use \Dhl\Versenden\Bcs\Api\Service\Cod;
use \Dhl\Versenden\Bcs\Api\Service\ServiceCollection;
use \Dhl\Versenden\Bcs\Api\Service\ServiceCollectionFactory;
use \Dhl\Versenden\Bcs\Api\Service\ServiceFactory;
use \Dhl\Versenden\Model\Config;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\DataObject;
use \Magento\Framework\Event;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\OfflinePayments\Model\Cashondelivery;
use \Magento\OfflinePayments\Model\Checkmo;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * DisableCodPaymentObserverTest
 *
 * @category Dhl
 * @package  Dhl\Versenden\Test\Unit
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class DisableCodPaymentObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSession;

    /**
     * @var ConfigInterface|MockObject
     */
    private $config;

    /**
     * @var ServiceCollectionFactory|MockObject
     */
    private $serviceCollectionFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);

        $this->checkoutSession = $this->getMock(CheckoutSession::class, ['start', 'getQuote', 'destroy'], [], '', false);
        $this->config = $this->getMock(
            Config::class,
            ['getShipperCountry', 'canProcessMethod', 'isCodPaymentMethod', 'getEuCountryList'],
            [],
            '',
            false
        );
        $this->serviceCollectionFactory = $this->getMockBuilder('\Dhl\Versenden\Bcs\Api\Service\ServiceCollectionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function skipDisabledPaymentMethod()
    {
        /** @var DisableCodPaymentObserver $codObserver */
        $codObserver = $this->objectManager->getObject(DisableCodPaymentObserver::class);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMock(Observer::class, [], [], '', false);
        $eventMock = $this->getMock(Event::class, ['getData'], [], '', false);
        $resultMock = $this->getMock(DataObject::class, ['getData'], [], '', false);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getData')->with('result', null)->willReturn($resultMock);
        $resultMock->expects($this->once())->method('getData')->with('is_available', null)->willReturn(false);

        $observerMock->expects($this->never())
            ->method('getData')
            ->with('quote', null);

        $codObserver->execute($observerMock);
    }

    /**
     * @test
     */
    public function quoteUnavailable()
    {
        /** @var DisableCodPaymentObserver $codObserver */
        $codObserver = $this->objectManager->getObject(DisableCodPaymentObserver::class, [
            'checkoutSession' => $this->checkoutSession,
        ]);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMock(Observer::class, [], [], '', false);
        $eventMock = $this->getMock(Event::class, ['getData'], [], '', false);
        $resultMock = $this->getMock(DataObject::class, ['getData'], [], '', false);

        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getData')->with('result', null)->willReturn($resultMock);
        $resultMock->expects($this->once())->method('getData')->with('is_available', null)->willReturn(true);

        $observerMock->expects($this->once())
            ->method('getData')
            ->with('quote', null);

        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn(null);

        $codObserver->execute($observerMock);
    }

    /**
     * @test
     */
    public function observerReturnsVoidWhenShipmentIsNotProcessedWithDhl()
    {
        /** @var DisableCodPaymentObserver $codObserver */
        $codObserver = $this->objectManager->getObject(DisableCodPaymentObserver::class, [
            'checkoutSession' => $this->checkoutSession,
            'config' => $this->config,
        ]);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMock(Observer::class, [], [], '', false);
        $eventMock = $this->getMock(Event::class, ['getData'], [], '', false);
        $resultMock = $this->getMock(DataObject::class, ['getData'], [], '', false);
        $paymentMethodMock = $this->getMock(Cashondelivery::class, [], [], '', false);

        $observerMock->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);

        $returnValueMap = [
            ['result', null, $resultMock],
            ['method_instance', null, $paymentMethodMock],
        ];
        $eventMock->expects($this->exactly(2))->method('getData')->willReturnMap($returnValueMap);
        $resultMock->expects($this->once())->method('getData')->with('is_available', null)->willReturn(true);

        $observerMock->expects($this->once())
            ->method('getData')
            ->with('quote', null);

        $quote = new DataObject(['shipping_address' => new DataObject(['shipping_method' => 'flatrate_flatrate'])]);
        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->config
            ->expects($this->once())
            ->method('canProcessMethod')
            ->willReturn(false);

        $this->config
            ->expects($this->never())
            ->method('isCodPaymentMethod');

        $codObserver->execute($observerMock);
    }

    /**
     * @test
     */
    public function observerReturnsVoidWhenPaymentIsNotCod()
    {
        /** @var DisableCodPaymentObserver $codObserver */
        $codObserver = $this->objectManager->getObject(DisableCodPaymentObserver::class, [
            'checkoutSession' => $this->checkoutSession,
            'config' => $this->config,
        ]);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMock(Observer::class, [], [], '', false);
        $eventMock = $this->getMock(Event::class, ['getData'], [], '', false);
        $resultMock = $this->getMock(DataObject::class, ['getData'], [], '', false);
        $paymentMethodMock = $this->getMock(Checkmo::class, [], [], '', false);

        $observerMock->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);

        $returnValueMap = [
            ['result', null, $resultMock],
            ['method_instance', null, $paymentMethodMock],
        ];
        $eventMock->expects($this->exactly(2))->method('getData')->willReturnMap($returnValueMap);
        $resultMock->expects($this->once())->method('getData')->with('is_available', null)->willReturn(true);

        $observerMock->expects($this->once())
            ->method('getData')
            ->with('quote', null);

        $quote = new DataObject(['shipping_address' => new DataObject(['shipping_method' => 'flatrate_flatrate'])]);
        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->config
            ->expects($this->once())
            ->method('canProcessMethod')
            ->willReturn(true);

        $this->config
            ->expects($this->once())
            ->method('isCodPaymentMethod')
            ->willReturn(false);

        $this->config
            ->expects($this->never())
            ->method('getShipperCountry');

        $codObserver->execute($observerMock);
    }

    /**
     * @test
     */
    public function codIsNotAvailable()
    {
        $shipperCountry = 'DE';
        $recipientCountry = 'NZ';
        $euCountryList = ['DE', 'AT', 'PL'];

        /** @var DisableCodPaymentObserver $codObserver */
        $codObserver = $this->objectManager->getObject(DisableCodPaymentObserver::class, [
            'checkoutSession' => $this->checkoutSession,
            'config' => $this->config,
            'serviceCollectionFactory' => $this->serviceCollectionFactory,
        ]);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMock(Observer::class, [], [], '', false);
        $eventMock = $this->getMock(Event::class, ['getData'], [], '', false);
        $resultMock = $this->getMock(DataObject::class, ['getData', 'setData'], [], '', false);
        $paymentMethodMock = $this->getMock(Checkmo::class, [], [], '', false);

        $observerMock->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);

        $returnValueMap = [
            ['result', null, $resultMock],
            ['method_instance', null, $paymentMethodMock],
        ];
        $eventMock->expects($this->exactly(2))->method('getData')->willReturnMap($returnValueMap);
        $resultMock->expects($this->once())->method('getData')->with('is_available', null)->willReturn(true);

        $observerMock->expects($this->once())
            ->method('getData')
            ->with('quote', null);

        $shippingAddress = new DataObject([
            'shipping_method' => 'flatrate_flatrate',
            'country_id' => $recipientCountry,
        ]);
        $quote = new DataObject(['shipping_address' => $shippingAddress]);
        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->config
            ->expects($this->once())
            ->method('canProcessMethod')
            ->willReturn(true);

        $this->config
            ->expects($this->once())
            ->method('isCodPaymentMethod')
            ->willReturn(true);

        $this->config
            ->expects($this->once())
            ->method('getShipperCountry')
            ->willReturn($shipperCountry);

        $this->config
            ->expects($this->once())
            ->method('getEuCountryList')
            ->willReturn($euCountryList);

        /** @var ServiceCollection $serviceCollectionMock */
        $codService = ServiceFactory::get(Cod::CODE);
        $serviceCollectionMock = $this->getMock(ServiceCollection::class, null);
        $serviceCollectionMock->offsetSet(Cod::CODE, $codService);
        $this->serviceCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($serviceCollectionMock);

        $resultMock->expects($this->once())->method('setData')->with('is_available', false);
        $codObserver->execute($observerMock);
    }

    /**
     * @test
     */
    public function codIsAvailable()
    {
        $shipperCountry = 'AT';
        $recipientCountry = 'DE';
        $euCountryList = ['DE', 'AT', 'PL'];

        /** @var DisableCodPaymentObserver $codObserver */
        $codObserver = $this->objectManager->getObject(DisableCodPaymentObserver::class, [
            'checkoutSession' => $this->checkoutSession,
            'config' => $this->config,
            'serviceCollectionFactory' => $this->serviceCollectionFactory,
        ]);

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->getMock(Observer::class, [], [], '', false);
        $eventMock = $this->getMock(Event::class, ['getData'], [], '', false);
        $resultMock = $this->getMock(DataObject::class, ['getData', 'setData'], [], '', false);
        $paymentMethodMock = $this->getMock(Checkmo::class, [], [], '', false);

        $observerMock->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);

        $returnValueMap = [
            ['result', null, $resultMock],
            ['method_instance', null, $paymentMethodMock],
        ];
        $eventMock->expects($this->exactly(2))->method('getData')->willReturnMap($returnValueMap);
        $resultMock->expects($this->once())->method('getData')->with('is_available', null)->willReturn(true);

        $observerMock->expects($this->once())
            ->method('getData')
            ->with('quote', null);

        $shippingAddress = new DataObject([
            'shipping_method' => 'flatrate_flatrate',
            'country_id' => $recipientCountry,
        ]);
        $quote = new DataObject(['shipping_address' => $shippingAddress]);
        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $this->config
            ->expects($this->once())
            ->method('canProcessMethod')
            ->willReturn(true);

        $this->config
            ->expects($this->once())
            ->method('isCodPaymentMethod')
            ->willReturn(true);

        $this->config
            ->expects($this->once())
            ->method('getShipperCountry')
            ->willReturn($shipperCountry);

        $this->config
            ->expects($this->once())
            ->method('getEuCountryList')
            ->willReturn($euCountryList);

        /** @var ServiceCollection $serviceCollectionMock */
        $codService = ServiceFactory::get(Cod::CODE);
        $serviceCollectionMock = $this->getMock(ServiceCollection::class, null);
        $serviceCollectionMock->offsetSet(Cod::CODE, $codService);
        $this->serviceCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($serviceCollectionMock);

        $resultMock->expects($this->once())->method('setData')->with('is_available', true);
        $codObserver->execute($observerMock);
    }
}
