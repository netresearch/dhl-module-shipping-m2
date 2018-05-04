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
namespace Dhl\Shipping\Model\Shipping;

use Dhl\Shipping\Test\Provider\ShipmentResponseProvider;
use Dhl\Shipping\Webservice\Gateway;
use \Magento\Framework\DataObject;
use \Magento\Framework\DataObjectFactory;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use \Magento\Shipping\Model\Shipment\Request as ShipmentRequest;
use \Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * CarrierTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Unit
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
     * @var DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactory;

    /**
     * @var Gateway|\PHPUnit_Framework_MockObject_MockObject
     */
    private $webserviceGateway;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);

        $testName = $this->getName(false);
        $factoryIsInvoked = in_array($testName, ['shipmentRequestSuccess', 'shipmentRequestError']);
        $dataObject = $this->objectManager->getObject(DataObject::class);
        $this->dataObjectFactory = $this->getMockBuilder(DataObjectFactory::class)
                                        ->setMethods(['create'])
                                        ->disableOriginalConstructor()
                                        ->getMock();
        $this->dataObjectFactory
            ->expects($this->exactly((int)$factoryIsInvoked))
            ->method('create')
            ->willReturn($dataObject);

        $this->webserviceGateway = $this->getMockBuilder(Gateway::class)
                                        ->setMethods(['createLabels'])
                                        ->disableOriginalConstructor()
                                        ->getMock();
    }

    /**
     * @test
     */
    public function loadCarrier()
    {
        $interface = CarrierInterface::class;
        $className = Carrier::class;

        try {
            $carrier = $this->objectManager->getObject($className);
        } catch (\ReflectionException $e) {
            $carrier = null;
        }

        $this->assertInstanceOf($interface, $carrier);
        $this->assertInstanceOf(AbstractCarrierOnline::class, $carrier);
    }

    /**
     * @test
     */
    public function carrierCode()
    {
        /** @var Carrier $carrier */
        $carrier = $this->objectManager->getObject(Carrier::class);
        $this->assertSame(Carrier::CODE, $carrier->getCarrierCode());
    }

    /**
     * @test
     */
    public function collectRates()
    {
        /** @var Carrier $carrier */
        $carrier = $this->objectManager->getObject(Carrier::class);
        /** @var \Magento\Quote\Model\Quote\Address\RateRequest $rateRequest */
        $rateRequest = $this->objectManager->getObject(\Magento\Quote\Model\Quote\Address\RateRequest::class);

        $rates = $carrier->collectRates($rateRequest);
        $this->assertNull($rates);
    }

    /**
     * @test
     */
    public function getAllowedMethods()
    {
        /** @var Carrier $carrier */
        $carrier = $this->objectManager->getObject(Carrier::class);
        $methods = $carrier->getAllowedMethods();
        $this->assertInternalType('array', $methods);
        $this->assertCount(0, $methods);
    }

    /**
     * @test
     */
    public function shipmentRequestSuccess()
    {
        $this->markTestIncomplete('Carrier is created without object dependencies');

        $incrementId = '1001';
        $packageId = '7';

        $sequenceNumber = "$incrementId-$packageId";
        $mockResponse = ShipmentResponseProvider::provideSingleSuccessResponse($sequenceNumber);

        $this->webserviceGateway
            ->expects($this->once())
            ->method('createLabels')
            ->willReturn($mockResponse);

        $order = $this->objectManager->getObject(DataObject::class, ['data' => [
            'increment_id' => $incrementId,
        ]]);
        $shipment = $this->objectManager->getObject(DataObject::class, ['data' => [
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
        $request = $this->objectManager->getObject(ShipmentRequest::class, ['data' => [
            'packages' => [$packageId => $package],
            'order_shipment' => $shipment,
        ]]);

        /** @var Carrier $carrier */
        $carrier = $this->objectManager->getObject(Carrier::class, [
            'dataObjectFactory' => $this->dataObjectFactory,
            'webserviceGateway' => $this->webserviceGateway,
        ]);
        $response = $carrier->requestToShipment($request);
        $this->assertInstanceOf(DataObject::class, $response);

        $this->assertArrayHasKey('info', $response->getData());
        $this->assertInternalType('array', $response->getData('info'));
        $this->assertCount(1, $response->getData('info'));
        $this->assertArrayHasKey(0, $response->getData('info'));

        $this->assertArrayHasKey('tracking_number', $response->getData('info/0'));
        $this->assertEquals(
            $mockResponse->getCreatedItem($sequenceNumber)->getTrackingNumber(),
            $response->getData('info/0/tracking_number')
        );

        $this->assertArrayHasKey('label_content', $response->getData('info/0'));
        $this->assertEquals(
            $mockResponse->getCreatedItem($sequenceNumber)->getLabel(),
            $response->getData('info/0/label_content')
        );
    }

    /**
     * @test
     */
    public function shipmentRequestError()
    {
        $incrementId = '1001';
        $packageId = '7';

        $mockResponse = ShipmentResponseProvider::provideSingleErrorResponse();

        $this->webserviceGateway
            ->expects($this->once())
            ->method('createLabels')
            ->willReturn($mockResponse);

        $order = $this->objectManager->getObject(
            DataObject::class,
            ['data' => [
                'increment_id' => $incrementId,
            ]]
        );
        $shipment = $this->objectManager->getObject(
            DataObject::class,
            ['data' => [
                'order' => $order,
            ]]
        );
        $package = [
            'params' => [
                'container' => 'foo',
                'weight' => 42
            ],
            'items' => [],
        ];

        /** @var ShipmentRequest $request */
        $request = $this->objectManager->getObject(
            ShipmentRequest::class,
            ['data' => [
                'packages' => [$packageId => $package],
                'order_shipment' => $shipment,
            ]]
        );

        /** @var Carrier $carrier */
        $carrier = $this->objectManager->getObject(
            Carrier::class,
            [
                'dataObjectFactory' => $this->dataObjectFactory,
                'webserviceGateway' => $this->webserviceGateway,
            ]
        );
        $response = $carrier->requestToShipment($request);
        $this->assertInstanceOf(DataObject::class, $response);

        $this->assertArrayHasKey('errors', $response->getData());
        $this->assertInternalType('string', $response->getData('errors'));
        $this->assertEquals($mockResponse->getStatus()->getMessage(), $response->getData('errors'));
    }
}
