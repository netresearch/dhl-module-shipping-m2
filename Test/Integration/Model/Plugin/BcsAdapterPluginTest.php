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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Plugin;

use \Dhl\Shipping\Webservice\Client\BcsSoapClient;
use \Dhl\Shipping\Webservice\Exception\CreateShipmentStatusException;
use \Magento\TestFramework\Interception\PluginList;
use \Magento\TestFramework\ObjectManager;
use \Dhl\Shipping\Webservice\Adapter\BcsAdapter as ApiAdapter;
use \Dhl\Shipping\Webservice\Logger;
use \Dhl\Shipping\Webservice\ResponseParser\BcsResponseParser as ResponseParser;

/**
 * BcsAdapterPluginTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class BcsAdapterPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ApiAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bcsAdapter;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Dhl\Shipping\Webservice\BcsDataMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMapper;

    /**
     * set up mocks
     */
    public function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();

        $this->requestMapper = $this->getMockBuilder(\Dhl\Shipping\Webservice\BcsDataMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(Logger::class)
            ->setMethods(['wsDebug', 'log', 'wsWarning', 'wsError'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->addSharedInstance($this->logger, \Dhl\Shipping\Webservice\Logger::class);
    }

    /**
     * clean up after each test
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(PluginList::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\Plugin\BcsAdapterPlugin::class);

        parent::tearDown();
    }

    /**
     * @return \mixed[]
     */
    public function getValidOrderProvider()
    {
        $order = \Dhl\Shipping\Test\Provider\ShipmentOrderProvider::getValidOrder();
        return $order;
    }

    /**
     * When no errors occur, log debug
     *
     * @test
     * @dataProvider getValidOrderProvider
     *
     * @param \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder $shipmentOrder
     * @param mixed[] $expectation
     */
    public function logDebug($shipmentOrder, $expectation)
    {
        $this->markTestSkipped('Logging is being reworked.');

        $soapClientMock = $this->getMockBuilder(BcsSoapClient::class)
            ->setMethods(['createShipmentOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $soapClientMock
            ->expects($this->once())
            ->method('createShipmentOrder')
            ->willReturnSelf();

        $this->logger->expects($this->once())->method('wsDebug');
        $this->logger->expects($this->never())->method('wsWarning');
        $this->logger->expects($this->never())->method('wsError');

        $parserMock = $this->getMockBuilder(ResponseParser::class)
            ->setMethods(['parseCreateShipmentResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $parserMock
            ->expects($this->once())
            ->method('parseCreateShipmentResponse')
            ->will($this->returnValue($shipmentOrder->getSequenceNumber()));

        $apiAdapter = $this->objectManager->create(ApiAdapter::class, [
            'soapClient' => $soapClientMock,
            'dataMapper' => $this->requestMapper,
            'responseParser' => $parserMock
        ]);

        $response = $apiAdapter->createLabels([$shipmentOrder]);
        $this->assertEquals($expectation['sequenceNumber'], $response);
    }

    /**
     * @test
     * @dataProvider getValidOrderProvider
     *
     * @param \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder $shipmentOrder
     */
    public function logError($shipmentOrder)
    {
        $this->markTestSkipped('Logging is being reworked.');

        $soapFault = new \SoapFault('1', 'error');
        $soapClientMock = $this->getMockBuilder(BcsSoapClient::class)
            ->setMethods(['createShipmentOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $soapClientMock
            ->expects($this->once())
            ->method('createShipmentOrder')
            ->willThrowException($soapFault);

        $this->logger->expects($this->never())->method('wsDebug');
        $this->logger->expects($this->never())->method('wsWarning');
        $this->logger->expects($this->once())->method('wsError');

        $bcsAdapter = $this->objectManager->create(ApiAdapter::class, [
            'soapClient' => $soapClientMock,
            'dataMapper' => $this->requestMapper,
        ]);

        $this->expectException(\SoapFault::class);
        $bcsAdapter->createLabels([$shipmentOrder]);
    }

    /**
     * @test
     * @dataProvider getValidOrderProvider
     */
    public function logWarning($shipmentOrder)
    {
        $this->markTestSkipped('Logging is being reworked.');

        /** @var CreateShipmentStatusException|\PHPUnit_Framework_MockObject_MockObject $wsException */
        $wsException = $this->getMockBuilder(CreateShipmentStatusException::class)
            ->disableOriginalConstructor()
            ->getMock();

        $soapClientMock = $this->getMockBuilder(BcsSoapClient::class)
            ->setMethods(['createShipmentOrder'])
            ->disableOriginalConstructor()
            ->getMock();
        $soapClientMock
            ->expects($this->once())
            ->method('createShipmentOrder')
            ->willThrowException($wsException);

        $this->logger->expects($this->never())->method('wsDebug');
        $this->logger->expects($this->once())->method('wsWarning');
        $this->logger->expects($this->never())->method('wsError');

        $this->bcsAdapter = $this->objectManager->create(ApiAdapter::class, [
            'soapClient' => $soapClientMock,
            'dataMapper' => $this->requestMapper,
        ]);

        $this->expectException(CreateShipmentStatusException::class);
        $this->bcsAdapter->createLabels([$shipmentOrder]);
    }
}
