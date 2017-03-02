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

use Magento\Framework\DataObject;
use \Magento\TestFramework\ObjectManager;
use \Magento\Shipping\Model\Shipment\Request as ShipmentRequest;

/**
 * ConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /** @var  Carrier */
    private $model;

    /**
     * @var \Dhl\Shipping\Api\Webservice\GatewayInterface
     */
    private $webserviceGateway;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;


    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->model = $this->objectManager->create(Carrier::class);
    }

    /**
     * @test
     */
    public function collectRates()
    {
        $request = $this->objectManager->create(\Magento\Quote\Model\Quote\Address\RateRequest::class);
        $this->assertNull($this->model->collectRates($request));
    }

    /**
     * @test
     */
    public function getAllowedMethods()
    {
        $result = $this->model->getAllowedMethods();
        $this->assertTrue(is_array($result));
        $this->assertCount(0, $result);
    }

    /**
     * @test
     */
    public function getTrackingInfo()
    {
        /** @var \Magento\Shipping\Model\Tracking\Result\Status $result */
        $result = $this->model->getTrackingInfo('1234');
        $this->assertInstanceOf('\Magento\Shipping\Model\Tracking\Result\Status', $result);
        $this->assertEquals('dhlshipping', $result->getData('carrier'));
        $this->assertEquals('DHL', $result->getData('carrier_title'));
        $this->assertEquals('1234', $result->getData('tracking'));
        $this->assertEquals('http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=1234', $result->getData('url'));
    }

    /**
 * @test
 */
    public function requestToShipmentError()
    {

        $package = [
            'params' => [
                'container' => 'foo',
                'weight' => 42
            ],
            'items' => [],
        ];

        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->setIncrementId(12);
        $orderShipment = $this->objectManager->create(Carrier::class);
        $orderShipment->setOrder($order);
        /** @var ShipmentRequest $request */
        $request = $this->objectManager->get(ShipmentRequest::class);
        $request->setOrderShipment($orderShipment);
        $request->setData('packages', [$package]);

        $responseStatus = $this->objectManager->create(
            \Dhl\Shipping\Webservice\ResponseType\Generic\ResponseStatus::class,
            [
                'code' => 2,
                'text' => 'foo',
                'message' => 'bar'
            ]);
        $webserviceResponse = $this->objectManager->create(
            \Dhl\Shipping\Webservice\ResponseType\CreateShipmentResponseCollection::class,
            ['status' => $responseStatus]
        );

        $webServiceMock = $this->getMock(\Dhl\Shipping\Webservice\Gateway::class, ['createLabels'], [], '', false);
        $webServiceMock
            ->expects($this->any())
            ->method('createLabels')
            ->will($this->returnValue($webserviceResponse));

        $this->model = $this->objectManager->create(Carrier::class, [
            'webserviceGateway' => $webServiceMock
        ]);

        $response = $this->model->requestToShipment($request);
        $this->assertEquals('bar', $response->getErrors());
    }

    /**
     * @test
     */
    public function requestToShipment()
    {

        $package= [
            'params' => [
                'container' => 'foo',
                'weight' => 42
            ],
            'items' => [],
        ];

        $order = $this->objectManager->create(\Magento\Sales\Model\Order::class);
        $order->setIncrementId(12);
        $orderShipment = $this->objectManager->create(Carrier::class);
        $orderShipment->setOrder($order);
        /** @var ShipmentRequest $request */
        $request = $this->objectManager->get(ShipmentRequest::class);
        $request->setOrderShipment($orderShipment);
        $request->setData('packages', [$package]);

        $itemStatus = $this->objectManager->create(
            \Dhl\Shipping\Webservice\ResponseType\Generic\ItemStatus::class,
            [
                'identifier' => 'foo',
                'code'=> 0,
                'text' => 'foo',
                'message' => 'bar'
            ]);
        $label = $this->objectManager->create(
            \Dhl\Shipping\Webservice\ResponseType\CreateShipment\Label::class,
            [
                'status' => $itemStatus,
                'sequenceNumber' => '12',
                'trackingNumber' => 'tr12qa',
                'label' => 'label',
                'returnLabel' => 'returnLabel',
                'exportLabel' => 'exportLabel',
                'codLabel' => 'codLabel'
            ]);

        $webserviceResponse = $this->objectManager->create(
            \Dhl\Shipping\Webservice\ResponseType\CreateShipmentResponseCollection::class,
            ['status' => $itemStatus]
        );
        $sequenceNumber = '12-0';
        $webserviceResponse[$sequenceNumber] = $label;

        $webServiceMock = $this->getMock(\Dhl\Shipping\Webservice\Gateway::class, ['createLabels'], [], '', false);
        $webServiceMock
            ->expects($this->any())
            ->method('createLabels')
            ->will($this->returnValue($webserviceResponse));

        $this->model = $this->objectManager->create(Carrier::class, [
            'webserviceGateway' => $webServiceMock
        ]);

        $response = $this->model->requestToShipment($request);
        $info = $response->getInfo();
        $this->assertTrue(is_array($info));
        $this->assertArrayHasKey('tracking_number',$info[0]);
        $this->assertArrayHasKey('label_content',$info[0]);
        $this->assertEquals('tr12qa',$info[0]['tracking_number']);
        $this->assertEquals('label',$info[0]['label_content']);
    }


}
