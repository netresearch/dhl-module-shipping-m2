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
namespace Dhl\Versenden\Model\Shipping;

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
 * @package  Dhl\Versenden\Test\Unit
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CarrierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);

        $this->dataObjectFactory = $this->getMock(
            'Magento\Framework\DataObjectFactory',
            ['create'],
            [],
            '',
            false
        );
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
    public function requestToShipment()
    {
        $dataObject = $this->getMock(DataObject::class, ['setData']);
        $dataObject->expects($this->once())
            ->method('setData')
            ->with('info', $this->isType('array'));

        $this->dataObjectFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($dataObject);

        /** @var Carrier $carrier */
        $carrier = $this->objectManager->getObject(Carrier::class, [
            'dataObjectFactory' => $this->dataObjectFactory,
        ]);

        $package = [
            'params' => [
                'container' => 'foo',
                'weight' => 42
            ],
            'items' => [],
        ];
        /** @var ShipmentRequest $request */
        $request = $this->objectManager->getObject(ShipmentRequest::class);
        $request->setData('packages', [$package]);

        $response = $carrier->requestToShipment($request);
        $this->assertInstanceOf(DataObject::class, $response);

        $this->assertArrayHasKey('info', $response->getData());
        $this->assertInternalType('array', $response->getData('info'));
        $this->assertCount(1, $response->getData('info'));
        $this->assertArrayHasKey(0, $response->getData('info'));

        $this->assertArrayHasKey('tracking_number', $response->getData('info/0'));
        $this->assertEquals('', $response->getData('info/0/tracking_number'));

        $this->assertArrayHasKey('label_content', $response->getData('info/0'));
        $this->assertEquals('', $response->getData('info/0/label_content'));
    }
}
