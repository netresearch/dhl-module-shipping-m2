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
 * @category  Dhl
 * @package   Dhl\Shipping\Test\Integration\Model\Cron
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Cron;

use Dhl\Shipping\AutoCreate\LabelGenerator;
use Dhl\Shipping\AutoCreate\LabelGeneratorInterface;
use Dhl\Shipping\AutoCreate\OrderProvider;
use Dhl\Shipping\AutoCreate\OrderProviderInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Config\ModuleConfig;
use Dhl\Shipping\Model\Config\ServiceConfigInterface;
use Dhl\Shipping\Model\CreateShipment;
use Dhl\Shipping\Test\Fixture\OrderCollectionFixture;
use Magento\Cron\Model\Schedule;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoresConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/**
 * AutoCreateTest
 */
class AutoCreateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AutoCreate
     */
    private $autoCreate;

    /**
     * @var ModuleConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleConfig;

    /**
     * @var ServiceConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $serviceConfig;

    /**
     * @var StoresConfig | \PHPUnit_Framework_MockObject_MockObject
     */
    private $storesConfig;

    /**
     * @var LabelGeneratorInterface| \PHPUnit_Framework_MockObject_MockObject
     */
    private $labelGenerator;

    /**
     * @var CreateShipment | \PHPUnit_Framework_MockObject_MockObject
     */
    private $createShipment;

    public static function createOrdersFixtures()
    {
        OrderCollectionFixture::createOrdersFixture();
    }

    public static function createOrdersRollback()
    {
        OrderCollectionFixture::createOrdersRollback();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();

        $this->moduleConfig = $this->getMockBuilder(ModuleConfig::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(
                                       [
                                           'canProcessRoute',
                                           'getDefaultProduct',
                                           'isCrossBorderRoute',
                                           'getAutoCreateOrderStatus'
                                       ]
                                   )
                                   ->getMock();
        $this->storesConfig = $this->getMockBuilder(StoresConfig::class)
                                   ->disableOriginalConstructor()
                                   ->setMethods(['getStoresConfigByPath'])
                                   ->getMock();

        $this->labelGenerator = $this->getMockBuilder(LabelGenerator::class)
                                     ->disableOriginalConstructor()
                                     ->setMethods(['create'])
                                     ->getMock();

        $this->createShipment = $this->objectManager->create(
            CreateShipment::class,
            [
                'labelGenerator' => $this->labelGenerator,
            ]
        );

        $orderProvider = $this->objectManager->create(OrderProvider::class, [
            'moduleConfig' => $this->moduleConfig,
            'storesConfig' => $this->storesConfig,
        ]);
        $this->objectManager->addSharedInstance($orderProvider, OrderProviderInterface::class);

        $this->autoCreate = $this->objectManager->create(
            AutoCreate::class,
            [
                'orderProvider' => $orderProvider,
                'createShipment' => $this->createShipment
            ]
        );
    }

    /**
     * @test
     * @magentoDataFixture createOrdersFixtures
     * @magentoConfigFixture default_store carriers/dhlshipping/default_shipping_product foo
     */
    public function testRun()
    {
        $this->moduleConfig->expects($this->once())
                           ->method('getAutoCreateOrderStatus')
                           ->will(
                               $this->returnValue(
                                   [
                                       Order::STATE_NEW,
                                       Order::STATE_PROCESSING
                                   ]
                               )
                           );

        $this->moduleConfig->expects($this->exactly(3))
                           ->method('canProcessRoute')
                           ->will($this->returnValue(true));
        $this->moduleConfig->expects($this->exactly(3))
                           ->method('isCrossBorderRoute')
                           ->will($this->returnValue(false));

        $this->storesConfig->expects($this->once())
                           ->method('getStoresConfigByPath')
                           ->with(ModuleConfigInterface::CONFIG_XML_PATH_AUTOCREATE_ENABLED)
                           ->will(
                               $this->returnValue(
                                   [
                                       0 => 1,
                                       1 => 1
                                   ]
                               )
                           );

        $schedule = $this->objectManager->get(Schedule::class);
        $this->autoCreate->run($schedule);

        $this->assertEquals(
            '3 shipments were created. 0 shipments could not be created.',
            $schedule->getData('messages')
        );
    }
}
