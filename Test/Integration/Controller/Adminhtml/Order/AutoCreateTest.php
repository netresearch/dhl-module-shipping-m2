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
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Controller\Adminhtml\Order;

use Dhl\Shipping\AutoCreate\LabelGenerator;
use Dhl\Shipping\AutoCreate\LabelGeneratorInterface;
use Dhl\Shipping\AutoCreate\OrderProvider;
use Dhl\Shipping\AutoCreate\OrderProviderInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Config\ModuleConfig;
use Dhl\Shipping\Model\Config\ServiceConfigInterface;
use Dhl\Shipping\Model\CreateShipment;
use Dhl\Shipping\Test\Fixture\OrderCollectionFixture;
use Magento\Framework\Message\Manager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Store\Model\StoresConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;

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

    /**
     * @var Manager
     */
    private $messageManager;

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

        $this->messageManager = $this->objectManager->create(Manager::class);

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
            ['labelGenerator' => $this->labelGenerator,]
        );

        /** @var Collection $orderCollection */
        $orderCollection = $this->objectManager->create(Collection::class);

        $filter = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCollection'])
            ->getMock();
        $filter->method('getCollection')->willReturn($orderCollection);

        $context = $this->objectManager->create(
            \Magento\Backend\App\Action\Context::class,
            ['messageManager' => $this->messageManager]
        );
        $this->autoCreate = $this->objectManager->create(
            AutoCreate::class,
            [
                'filter' => $filter,
                'createShipment' => $this->createShipment,
                'context' => $context,
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
        $this->autoCreate->execute();

        $this->assertEquals(
            'The label(s) for 4 order(s) could not be created. 3 label(s) were successfully created.',
            $this->messageManager->getMessages()->getItems()[0]->getText()
        );
    }
}
