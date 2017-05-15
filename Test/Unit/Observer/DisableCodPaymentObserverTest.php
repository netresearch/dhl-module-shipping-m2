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
 * @package   Dhl\Shipping\Test\Unit
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use \Dhl\Shipping\Api\Config\ModuleConfigInterface;
use \Dhl\Shipping\Api\Service\Cod;
use \Dhl\Shipping\Api\Service\ServiceCollection;
use \Dhl\Shipping\Api\Service\ServiceCollectionFactory;
use \Dhl\Shipping\Api\Service\ServiceFactory;
use Dhl\Shipping\Api\Webservice\BcsAccessDataInterface;
use \Dhl\Shipping\Model\Config\ModuleConfig;
use Dhl\Shipping\Webservice\BcsAccessData;
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
 * @package  Dhl\Shipping\Test\Unit
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
     * @var ModuleConfigInterface|MockObject
     */
    private $config;

    /**
     * @var BcsAccessDataInterface|MockObject
     */
    private $bcsAccessData;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSession;

    /**
     * @var ServiceCollectionFactory|MockObject
     */
    private $serviceCollectionFactory;

    protected function setUp()
    {
        parent::setUp();

        $testName = $this->getName(false);

        $this->objectManager = new ObjectManager($this);

        $this->config = $this->getMock(
            ModuleConfig::class,
            ['getShipperCountry', 'canProcessShipping', 'isCodPaymentMethod', 'getEuCountryList'],
            [],
            '',
            false
        );
        $this->bcsAccessData = $this->getMock(BcsAccessData::class, ['getProductCode'], [], '', false);
        $invokedCount = in_array($testName, ['codIsNotAvailable', 'codIsAvailable']);
        $returnValue = $testName === 'codIsNotAvailable'
            ? BcsAccessDataInterface::CODE_WELTPAKET
            : BcsAccessDataInterface::CODE_PAKET_NATIONAL;
        $this->bcsAccessData
            ->expects($this->exactly((int)$invokedCount))
            ->method('getProductCode')
            ->willReturn($returnValue);
        $this->checkoutSession = $this->getMock(CheckoutSession::class, ['start', 'getQuote', 'destroy'], [], '', false);
        $this->serviceCollectionFactory = $this->getMockBuilder(ServiceCollectionFactory::class)
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
            'config' => $this->config,
            'bcsAccessData' => $this->bcsAccessData,
            'checkoutSession' => $this->checkoutSession,
            'serviceCollectionFactory' => $this->serviceCollectionFactory,
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
            'config' => $this->config,
            'bcsAccessData' => $this->bcsAccessData,
            'checkoutSession' => $this->checkoutSession,
            'serviceCollectionFactory' => $this->serviceCollectionFactory,
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
            ->method('canProcessShipping')
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
            'config' => $this->config,
            'bcsAccessData' => $this->bcsAccessData,
            'checkoutSession' => $this->checkoutSession,
            'serviceCollectionFactory' => $this->serviceCollectionFactory,
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
            ->method('canProcessShipping')
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
        $recipientCountry = 'PL';
        $euCountryList = ['DE', 'AT', 'PL'];

        /** @var DisableCodPaymentObserver $codObserver */
        $codObserver = $this->objectManager->getObject(DisableCodPaymentObserver::class, [
            'config' => $this->config,
            'bcsAccessData' => $this->bcsAccessData,
            'checkoutSession' => $this->checkoutSession,
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
            ->method('canProcessShipping')
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
            ->expects($this->exactly(1))
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
            'config' => $this->config,
            'bcsAccessData' => $this->bcsAccessData,
            'checkoutSession' => $this->checkoutSession,
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
            ->method('canProcessShipping')
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
            ->expects($this->exactly(1))
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
