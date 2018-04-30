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
namespace Dhl\Shipping\Model\Shipping;

use \Dhl\Shipping\Test\Provider\ShipmentResponseProvider;
use \Dhl\Shipping\Webservice\Gateway;
use \Magento\Framework\DataObject;
use \Magento\Store\Model\StoreManager;
use \Magento\TestFramework\ObjectManager;
use \Magento\Shipping\Model\Shipment\Request as ShipmentRequest;

/**
 * CarrierTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CarrierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Carrier
     */
    private $carrier;

    /**
     * @var Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    private $webserviceGateway;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        /** @var StoreManager $storeManager */
        $storeManager = $this->objectManager->get(StoreManager::class);
        $this->carrier = $this->objectManager->create(Carrier::class, ['data' => [
            'store' => $storeManager->getDefaultStoreView()
        ]]);

        $this->webserviceGateway = $this->getMockBuilder(Gateway::class)
            ->setMethods(['createLabels', 'cancelLabels'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Assert that carrier does not provide any rates
     *
     * @test
     */
    public function collectRates()
    {
        $request = $this->objectManager->create(\Magento\Quote\Model\Quote\Address\RateRequest::class);
        $this->assertNull($this->carrier->collectRates($request));
    }

    /**
     * Assert that carrier does not provide shipping methods
     *
     * @test
     */
    public function getAllowedMethods()
    {
        $result = $this->carrier->getAllowedMethods();
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    /**
     * @test
     * @magentoConfigFixture default_store carriers/dhlshipping/title Business Customer Shipping
     * @magentoConfigFixture default_store shipping/origin/country_id DE
     */
    public function getBcsTrackingInfo()
    {
        $trackingNumber = '1234';
        $trackingUrl = sprintf(
            '%s%s',
            'https://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=',
            $trackingNumber
        );

        /** @var \Magento\Shipping\Model\Tracking\Result\Status $result */
        $result = $this->carrier->getTrackingInfo($trackingNumber);
        $this->assertInstanceOf(\Magento\Shipping\Model\Tracking\Result\Status::class, $result);
        $this->assertEquals(Carrier::CODE, $result->getData('carrier'));
        $this->assertEquals('Business Customer Shipping', $result->getData('carrier_title'));
        $this->assertEquals($trackingNumber, $result->getData('tracking'));
        $this->assertEquals($trackingUrl, $result->getData('url'));
    }

    /**
     * @test
     * @magentoConfigFixture default_store carriers/dhlshipping/title Global Shipping
     * @magentoConfigFixture default_store shipping/origin/country_id MY
     */
    public function getGlTrackingInfo()
    {
        $trackingNumber = '1234';
        $trackingUrl = sprintf(
            '%s%s',
            'https://webtrack.dhlglobalmail.com/?trackingnumber=',
            $trackingNumber
        );

        /** @var \Magento\Shipping\Model\Tracking\Result\Status $result */
        $result = $this->carrier->getTrackingInfo($trackingNumber);
        $this->assertInstanceOf(\Magento\Shipping\Model\Tracking\Result\Status::class, $result);
        $this->assertEquals(Carrier::CODE, $result->getData('carrier'));
        $this->assertEquals('Global Shipping', $result->getData('carrier_title'));
        $this->assertEquals($trackingNumber, $result->getData('tracking'));
        $this->assertEquals($trackingUrl, $result->getData('url'));
    }

    /**
     * @test
     * @magentoAppArea adminhtml
     */
    public function requestToShipmentError()
    {
        $incrementId = '1001';
        $packageId = '7';

        $response = ShipmentResponseProvider::provideSingleErrorResponse();
        $this->webserviceGateway
            ->expects($this->once())
            ->method('createLabels')
            ->willReturn($response);


        $orderId = $incrementId;
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->any())
              ->method('getId')
              ->willReturn($orderId);
        $order->expects($this->any())
            ->method('getShippingMethod')
            ->willReturn(new DataObject(['carrier_code' => 'foo']));

        $shipment = $this->objectManager->create(DataObject::class, ['data' => [
            'order' => $order,
        ]]);
        $package = [
            'params' => [
                'container' => 'foo',
                'weight' => 42
            ],
            'items' => [],
        ];

        /** @var ShipmentRequest $request */
        $request = $this->objectManager->create(ShipmentRequest::class, ['data' => [
            'packages' => [$packageId => $package],
            'order_shipment' => $shipment,
        ]]);

        $this->carrier = $this->objectManager->create(Carrier::class, [
            'webserviceGateway' => $this->webserviceGateway
        ]);

        $response = $this->carrier->requestToShipment($request);
        $this->assertEquals('Hard validation error occured.', $response->getData('errors'));
    }

    /**
     * @test
     * @magentoAppArea adminhtml
     */
    public function requestToShipmentSuccess()
    {
        $incrementId = '1001';
        $packageId = '7';
        $sequenceNumber = "$incrementId-$packageId";

        $response = ShipmentResponseProvider::provideSingleSuccessResponse($sequenceNumber);
        $this->webserviceGateway
            ->expects($this->once())
            ->method('createLabels')
            ->willReturn($response);

        /** @var \Magento\Sales\Model\Order $order */
        $orderId = 1;
        $order = $this->createMock(\Magento\Sales\Model\Order::class);
        $order->expects($this->any())
              ->method('getId')
              ->willReturn($orderId);
        $order->expects($this->any())
              ->method('getShippingMethod')
              ->willReturn(new DataObject(['carrier_code' => 'dhlshipping']));
        $order->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(1);
        $shipment = $this->objectManager->create(DataObject::class, ['data' => [
            'order' => $order,
        ]]);
        $package = [
            'params' => [
                'container' => 'foo',
                'weight' => 42
            ],
            'items' => [],
        ];

        /** @var ShipmentRequest $request */
        $request = $this->objectManager->create(ShipmentRequest::class, ['data' => [
            'packages' => [$packageId => $package],
            'order_shipment' => $shipment,
        ]]);

        $this->carrier = $this->objectManager->create(Carrier::class, [
            'webserviceGateway' => $this->webserviceGateway
        ]);

        $response = $this->carrier->requestToShipment($request);
        $info = $response->getData('info');
        $this->assertInternalType('array', $info);
        $this->assertCount(1, $info);
        $this->assertArrayHasKey('tracking_number', $info[0]);
        $this->assertArrayHasKey('label_content', $info[0]);
        $this->assertEquals('22222221337', $info[0]['tracking_number']);
        $this->assertContains('%PDF-1.4', $info[0]['label_content']);
    }
}
