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
 * @package   Dhl\Shipping\Test\Integration
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\DataObject;
use \Magento\Framework\Event;
use \Magento\Framework\Event\InvokerInterface;
use \Magento\Framework\Event\Observer;
use \Magento\TestFramework\Helper\Bootstrap;
use \Magento\TestFramework\ObjectManager;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * DisableCodPaymentObserverTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class DisableCodPaymentObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var InvokerInterface
     */
    private $invoker;

    /**
     * @var Observer
     */
    private $observer;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSession;

    /**
     * Init object manager
     */
    public function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->invoker = $this->objectManager->get(InvokerInterface::class);
        $this->observer = $this->objectManager->get(Observer::class);
        $this->checkoutSession = $this->getMockBuilder(CheckoutSession::class)
            ->setMethods(['start', 'getQuote', 'destroy'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->addSharedInstance($this->checkoutSession, CheckoutSession::class);
    }

    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(DisableCodPaymentObserver::class);

        parent::tearDown();
    }

    /**
     * Assert that payment methods that are already disabled are not changed by
     * the observer.
     *
     * @test
     */
    public function skipDisabledPaymentMethod()
    {
        $config = [
            'instance' => DisableCodPaymentObserver::class,
            'name' => 'shipping_disable_cod_payment',
        ];

        $isCurrentMethodAvailable = false;

        $checkResult = $this->objectManager->create(DataObject::class, ['data' => [
            'is_available' => $isCurrentMethodAvailable,
        ]]);
        $event = $this->objectManager->create(Event::class, ['data' => [
            'result' => $checkResult,
        ]]);
        $this->observer->setEvent($event);
        $this->invoker->dispatch($config, $this->observer);

        $this->assertSame(
            $isCurrentMethodAvailable,
            $this->observer->getEvent()->getData('result')->getData('is_available')
        );
    }

    /**
     * Assert that payment method availability is not changed in case the quote
     * is not available for whatever reason.
     *
     * @test
     */
    public function quoteUnavailable()
    {
        $config = [
            'instance' => DisableCodPaymentObserver::class,
            'name' => 'shipping_disable_cod_payment',
        ];

        $methodAvailability = [false, true];

        foreach ($methodAvailability as $isCurrentMethodAvailable) {
            $checkResult = $this->objectManager->create(DataObject::class, ['data' => [
                'is_available' => $isCurrentMethodAvailable,
            ]]);
            $event = $this->objectManager->create(Event::class, ['data' => [
                'result' => $checkResult,
            ]]);
            $this->observer->setEvent($event);
            $this->invoker->dispatch($config, $this->observer);

            $this->assertSame(
                $isCurrentMethodAvailable,
                $this->observer->getEvent()->getData('result')->getData('is_available')
            );
        }
    }

    /**
     * Assert that payment method availability is not changed in case the shipment
     * cannot be processed with DHL
     *
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id DE
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlcodmethods cashondelivery,nachnahme
     */
    public function observerReturnsVoidWhenShipmentIsNotProcessedWithDhl()
    {
        $config = [
            'instance' => DisableCodPaymentObserver::class,
            'name' => 'shipping_disable_cod_payment',
        ];

        $shippingMethod = 'foo_bar';
        $destCountryId = 'DE';
        $methodCode = 'cashondelivery';
        $methodAvailability = [false, true];

        $methodInstance = $this->objectManager->create(DataObject::class, ['data' => [
                'code' => $methodCode,
        ]]);

        $shippingAddress = $this->objectManager->create(DataObject::class, ['data' => [
            'shipping_method' => $shippingMethod,
            'country_id' => $destCountryId,
        ]]);
        $quote = $this->objectManager->create(DataObject::class, ['data' => [
            'shipping_address' => $shippingAddress,
        ]]);
        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        foreach ($methodAvailability as $isCurrentMethodAvailable) {
            $checkResult = $this->objectManager->create(DataObject::class, ['data' => [
                'is_available' => $isCurrentMethodAvailable,
            ]]);
            $event = $this->objectManager->create(Event::class, ['data' => [
                'result' => $checkResult,
                'method_instance' => $methodInstance,
            ]]);
            $this->observer->setEvent($event);
            $this->invoker->dispatch($config, $this->observer);

            $this->assertSame(
                $isCurrentMethodAvailable,
                $this->observer->getEvent()->getData('result')->getData('is_available')
            );
        }
    }

    /**
     * Assert that payment method availability is not changed in case the
     * payment method is no COD method.
     *
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id DE
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlcodmethods cashondelivery,nachnahme
     */
    public function observerReturnsVoidWhenPaymentIsNotCod()
    {
        $config = [
            'instance' => DisableCodPaymentObserver::class,
            'name' => 'shipping_disable_cod_payment',
        ];

        $shippingMethod = 'flatrate_flatrate';
        $destCountryId = 'DE';
        $methodCode = 'foopay';
        $methodAvailability = [false, true];

        $methodInstance = $this->objectManager->create(DataObject::class, ['data' => [
            'code' => $methodCode,
        ]]);

        $shippingAddress = $this->objectManager->create(DataObject::class, ['data' => [
            'shipping_method' => $shippingMethod,
            'country_id' => $destCountryId,
        ]]);
        $quote = $this->objectManager->create(DataObject::class, ['data' => [
            'shipping_address' => $shippingAddress,
        ]]);
        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        foreach ($methodAvailability as $isCurrentMethodAvailable) {
            $checkResult = $this->objectManager->create(DataObject::class, ['data' => [
                'is_available' => $isCurrentMethodAvailable,
            ]]);
            $event = $this->objectManager->create(Event::class, ['data' => [
                'result' => $checkResult,
                'method_instance' => $methodInstance,
            ]]);
            $this->observer->setEvent($event);
            $this->invoker->dispatch($config, $this->observer);

            $this->assertSame(
                $isCurrentMethodAvailable,
                $this->observer->getEvent()->getData('result')->getData('is_available')
            );
        }
    }
    /**
     * Assert that payment method gets disabled in case the current route does
     * not support it.
     *
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id DE
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlcodmethods cashondelivery,nachnahme
     */
    public function codIsNotAvailable()
    {
        $config = [
            'instance' => DisableCodPaymentObserver::class,
            'name' => 'shipping_disable_cod_payment',
        ];

        $shippingMethod = 'flatrate_flatrate';
        $destCountryId = 'CL';
        $methodCode = 'cashondelivery';
        $methodAvailability = [false, true];

        $methodInstance = $this->objectManager->create(DataObject::class, ['data' => [
            'code' => $methodCode,
        ]]);

        $shippingAddress = $this->objectManager->create(DataObject::class, ['data' => [
            'shipping_method' => $shippingMethod,
            'country_id' => $destCountryId,
        ]]);
        $quote = $this->objectManager->create(DataObject::class, ['data' => [
            'shipping_address' => $shippingAddress,
        ]]);
        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        foreach ($methodAvailability as $isCurrentMethodAvailable) {
            $checkResult = $this->objectManager->create(DataObject::class, ['data' => [
                'is_available' => $isCurrentMethodAvailable,
            ]]);
            $event = $this->objectManager->create(Event::class, ['data' => [
                'result' => $checkResult,
                'method_instance' => $methodInstance,
            ]]);
            $this->observer->setEvent($event);
            $this->invoker->dispatch($config, $this->observer);

            $this->assertFalse($this->observer->getEvent()->getData('result')->getData('is_available'));
        }
    }

    /**
     * Assert that payment method remains enabled in case the current route does
     * support it.
     *
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id AT
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlcodmethods cashondelivery,nachnahme
     */
    public function codIsAvailable()
    {
        $config = [
            'instance' => DisableCodPaymentObserver::class,
            'name' => 'shipping_disable_cod_payment',
        ];

        $shippingMethod = 'flatrate_flatrate';
        $destCountryId = 'DE';
        $methodCode = 'nachnahme';
        $methodAvailability = [false, true];

        $methodInstance = $this->objectManager->create(DataObject::class, ['data' => [
            'code' => $methodCode,
        ]]);

        $shippingAddress = $this->objectManager->create(DataObject::class, ['data' => [
            'shipping_method' => $shippingMethod,
            'country_id' => $destCountryId,
        ]]);
        $quote = $this->objectManager->create(DataObject::class, ['data' => [
            'shipping_address' => $shippingAddress,
        ]]);
        $this->checkoutSession
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        foreach ($methodAvailability as $isCurrentMethodAvailable) {
            $checkResult = $this->objectManager->create(DataObject::class, ['data' => [
                'is_available' => $isCurrentMethodAvailable,
            ]]);
            $event = $this->objectManager->create(Event::class, ['data' => [
                'result' => $checkResult,
                'method_instance' => $methodInstance,
            ]]);
            $this->observer->setEvent($event);
            $this->invoker->dispatch($config, $this->observer);

            $this->assertSame(
                $isCurrentMethodAvailable,
                $this->observer->getEvent()->getData('result')->getData('is_available')
            );
        }
    }
}
