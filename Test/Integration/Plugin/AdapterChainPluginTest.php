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
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Plugin;

use \Dhl\Shipping\Test\Provider\ShipmentOrderProvider;
use \Dhl\Shipping\Webservice\Adapter\AdapterChain;
use \Dhl\Shipping\Webservice\Adapter\BcsAdapter;
use \Dhl\Shipping\Webservice\Client\BcsSoapClient;
use \Dhl\Shipping\Webservice\Client\GlRestClient;
use \Dhl\Shipping\Webservice\Exception\ApiCommunicationException;
use \Dhl\Shipping\Webservice\Exception\ApiOperationException;
use \Dhl\Shipping\Webservice\Logger;
use \Magento\TestFramework\Interception\PluginList;
use \Magento\TestFramework\ObjectManager;

/**
 * AdapterChainPluginTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AdapterChainPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * set up mocks
     */
    public function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();

        $this->logger = $this->getMockBuilder(Logger::class)
                             ->setMethods(['wsDebug', 'log', 'wsWarning', 'wsError'])
                             ->disableOriginalConstructor()
                             ->getMock();
        $this->objectManager->addSharedInstance($this->logger, Logger::class);
    }

    /**
     * clean up after each test
     */
    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(PluginList::class);
        $this->objectManager->removeSharedInstance(AdapterChainPlugin::class);

        parent::tearDown();
    }

    /**
     * @return \mixed[]
     */
    public function getValidOrderProvider()
    {
        $order = ShipmentOrderProvider::getValidOrder();

        return $order;
    }

    /**
     * When no errors occur, log debug
     *
     * @test
     * @magentoAppArea adminhtml
     * @dataProvider   getValidOrderProvider
     *
     * @param \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder $shipmentOrder
     * @param mixed[]                                                           $expectation
     */
    public function logDebug($shipmentOrder, $expectation)
    {
        // Mock ApiAdapter
        $bcsApiAdapter = $this->getMockBuilder(BcsAdapter::class)
                              ->setMethods(['createShipmentOrders'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $bcsApiAdapter
            ->expects($this->once())
            ->method('createShipmentOrders')
            ->willReturn(['sequenceNumber' => '1010']);
        $this->objectManager->addSharedInstance($bcsApiAdapter, BcsAdapter::class);

        //Mock HttpClient
        $soapClientMock = $this->getMockBuilder(BcsSoapClient::class)
                               ->setMethods(['getLastRequest'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $soapClientMock
            ->expects($this->once())
            ->method('getLastRequest')
            ->willReturnSelf();
        $this->objectManager->addSharedInstance($soapClientMock, BcsSoapClient::class);
        $restClientMock = $this->getMockBuilder(GlRestClient::class)
                               ->setMethods(['getLastRequest'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $restClientMock
            ->expects($this->once())
            ->method('getLastRequest')
            ->willReturnSelf();
        $this->objectManager->addSharedInstance($restClientMock, GlRestClient::class);

        $this->logger->expects($this->exactly(2))->method('wsDebug');
        $this->logger->expects($this->never())->method('wsWarning');
        $this->logger->expects($this->never())->method('wsError');

        $adapterChain = $this->objectManager->create(AdapterChain::class);
        $response     = $adapterChain->createLabels([$shipmentOrder]);
        $this->assertEquals($expectation['sequenceNumber'], $response['sequenceNumber']);

        $this->objectManager->removeSharedInstance(BcsAdapter::class);
        $this->objectManager->removeSharedInstance(BcsSoapClient::class);
        $this->objectManager->removeSharedInstance(GlRestClient::class);
    }

    /**
     * @test
     * @magentoAppArea adminhtml
     * @dataProvider   getValidOrderProvider
     *
     * @param \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder $shipmentOrder
     */
    public function logError($shipmentOrder)
    {
        $exception = new ApiCommunicationException('1');
        // Mock ApiAdapter
        $bcsApiAdapter = $this->getMockBuilder(BcsAdapter::class)
                              ->setMethods(['createShipmentOrders'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $bcsApiAdapter
            ->expects($this->once())
            ->method('createShipmentOrders')
            ->willThrowException($exception);
        $this->objectManager->addSharedInstance($bcsApiAdapter, BcsAdapter::class);

        //Mock HttpClient
        $soapClientMock = $this->getMockBuilder(BcsSoapClient::class)
                               ->setMethods(['getLastRequest'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $soapClientMock
            ->expects($this->once())
            ->method('getLastRequest')
            ->willReturnSelf();
        $this->objectManager->addSharedInstance($soapClientMock, BcsSoapClient::class);

        $this->logger->expects($this->never())->method('wsDebug');
        $this->logger->expects($this->never())->method('wsWarning');
        $this->logger->expects($this->once())->method('wsError');

        $adapterChain = $this->objectManager->create(AdapterChain::class);
        $this->expectException(ApiCommunicationException::class);
        $adapterChain->createLabels([$shipmentOrder]);

        $this->objectManager->removeSharedInstance(BcsAdapter::class);
        $this->objectManager->removeSharedInstance(BcsSoapClient::class);
    }

    /**
     * @test
     * @magentoAppArea adminhtml
     * @dataProvider   getValidOrderProvider
     *
     * @param \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder $shipmentOrder
     */
    public function logWarning($shipmentOrder)
    {
        $exception = new ApiOperationException('1');
        // Mock ApiAdapter
        $bcsApiAdapter = $this->getMockBuilder(BcsAdapter::class)
                              ->setMethods(['createShipmentOrders'])
                              ->disableOriginalConstructor()
                              ->getMock();
        $bcsApiAdapter
            ->expects($this->once())
            ->method('createShipmentOrders')
            ->willThrowException($exception);
        $this->objectManager->addSharedInstance($bcsApiAdapter, BcsAdapter::class);

        $restClientMock = $this->getMockBuilder(GlRestClient::class)
                               ->setMethods(['getLastRequest'])
                               ->disableOriginalConstructor()
                               ->getMock();
        $restClientMock
            ->expects($this->once())
            ->method('getLastRequest')
            ->willReturnSelf();
        $this->objectManager->addSharedInstance($restClientMock, GlRestClient::class);

        $this->logger->expects($this->never())->method('wsDebug');
        $this->logger->expects($this->once())->method('wsWarning');
        $this->logger->expects($this->never())->method('wsError');

        $adapterChain = $this->objectManager->create(AdapterChain::class);
        $this->expectException(ApiOperationException::class);
        $adapterChain->createLabels([$shipmentOrder]);

        $this->objectManager->removeSharedInstance(BcsAdapter::class);
        $this->objectManager->removeSharedInstance(GlRestClient::class);
    }
}
