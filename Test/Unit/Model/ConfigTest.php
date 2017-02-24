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
namespace Dhl\Shipping\Model;

use \Dhl\Shipping\Api\Config\ModuleConfigInterface;
use \Dhl\Shipping\Model\Config\ModuleConfig;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Config\Storage\WriterInterface;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Shipping\Helper\Carrier as CarrierHelper;
use \Magento\Shipping\Model\Config as ShippingConfig;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * ConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Unit
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $store;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var WriterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configWriter;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);

        $this->store = $this->getMock('Magento\Store\Model\Store', ['getId'], [], '', false);
        $this->storeManager = $this->getMock('Magento\Store\Model\StoreManager', ['getStore'], [], '', false);

        $this->scopeConfig = $this->getMock('Magento\Framework\App\Config', ['getValue'], [], '', false);
        $this->configWriter = $this->getMock('Magento\Framework\App\Config\Storage\Writer', ['save'], [], '', false);
    }

    /**
     * @test
     */
    public function loadConfigModel()
    {
        $interface = ModuleConfigInterface::class;
        $className = ModuleConfig::class;

        try {
            $config = $this->objectManager->getObject($className);
        } catch (\ReflectionException $e) {
            $config = null;
        }

        $this->assertInstanceOf($interface, $config);
    }

    /**
     * @test
     */
    public function isLoggingEnabled()
    {
        $isLoggingEnabled = true;
        $debugLogLevel    = \Monolog\Logger::DEBUG;
        $errorLogLevel    = \Monolog\Logger::ERROR;
        $warningLogLevel  = \Monolog\Logger::WARNING;

        $this->scopeConfig
            ->expects($this->any())
            ->method('getValue')
            ->willReturnOnConsecutiveCalls(
                // (1) check if debug log is enabled with config DEBUG
                $isLoggingEnabled,
                $debugLogLevel,
                // (2) check if debug log is disabled with config WARNING
                $isLoggingEnabled,
                $warningLogLevel,
                // (3) check if warning log is disabled with config ERROR
                $isLoggingEnabled,
                $errorLogLevel
            );

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'scopeConfig' => $this->scopeConfig
        ]);

        $this->assertTrue($config->isLoggingEnabled());
        $this->assertFalse($config->isLoggingEnabled($debugLogLevel));
        $this->assertFalse($config->isLoggingEnabled($warningLogLevel));
    }

    /**
     * @test
     */
    public function isSandboxModeEnabled()
    {
        $this->scopeConfig
            ->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnOnConsecutiveCalls([true, false]);

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'scopeConfig'  => $this->scopeConfig
        ]);

        $this->assertTrue($config->isSandboxModeEnabled());
        $this->assertFalse($config->isSandboxModeEnabled());
    }

    /**
     * @test
     */
    public function getShipperCountry()
    {
        $defaultCountry = 'DE';
        $storeOneCountry = 'AT';

        $returnValueMap = [
            [ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $defaultCountry],
            [ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, ScopeInterface::SCOPE_STORE, 'store_one', $storeOneCountry],
        ];

        $this->scopeConfig
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configWriter' => $this->configWriter,
            'scopeConfig' => $this->scopeConfig
        ]);

        $this->assertSame($defaultCountry, $config->getShipperCountry());
        $this->assertSame($storeOneCountry, $config->getShipperCountry('store_one'));
    }

    /**
     * @test
     */
    public function getEuCountryList()
    {
        $de = 'DE';
        $at = 'AT';
        $pl = 'PL';

        $defaultList = "$de,$at";
        $storeOneList = $pl;

        $returnValueMap = [
            [CarrierHelper::XML_PATH_EU_COUNTRIES_LIST, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $defaultList],
            [CarrierHelper::XML_PATH_EU_COUNTRIES_LIST, ScopeInterface::SCOPE_STORE, 'store_one', $storeOneList],
        ];

        $this->scopeConfig
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configWriter' => $this->configWriter,
            'scopeConfig' => $this->scopeConfig
        ]);

        $list = $config->getEuCountryList();
        $this->assertInternalType('array', $list);
        $this->assertCount(2, $list);
        $this->assertContains($de, $list);
        $this->assertContains($at, $list);

        $list = $config->getEuCountryList('store_one');
        $this->assertInternalType('array', $list);
        $this->assertCount(1, $list);
        $this->assertContains($pl, $list);
    }

    /**
     * @test
     */
    public function getShippingMethods()
    {
        $defaultMethods  = 'flatrate_flatrate';
        $storeOneMethods = 'flatrate_flatrate,tablerate_bestway';
        $storeTwoMethods = null;

        $returnValueMap = [
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $defaultMethods],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, ScopeInterface::SCOPE_STORE, 'store_one', $storeOneMethods],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, ScopeInterface::SCOPE_STORE, 'store_two', $storeTwoMethods],
        ];

        $this->scopeConfig
            ->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configWriter' => $this->configWriter,
            'scopeConfig' => $this->scopeConfig
        ]);

        $shippingMethods = $config->getShippingMethods();
        $this->assertInternalType('array', $shippingMethods);
        $this->assertCount(1, $shippingMethods);
        $this->assertEquals($defaultMethods, implode(',', $shippingMethods));

        $shippingMethods = $config->getShippingMethods('store_one');
        $this->assertInternalType('array', $shippingMethods);
        $this->assertCount(2, $shippingMethods);
        $this->assertEquals($storeOneMethods, implode(',', $shippingMethods));

        $shippingMethods = $config->getShippingMethods('store_two');
        $this->assertInternalType('array', $shippingMethods);
        $this->assertCount(0, $shippingMethods);
    }

    /**
     * @test
     */
    public function getCodPaymentMethods()
    {
        $defaultMethods  = 'checkmo,payflowpro';
        $storeOneMethods = 'payflowpro';
        $storeTwoMethods = null;

        $returnValueMap = [
            [ModuleConfig::CONFIG_XML_PATH_CODMETHODS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $defaultMethods],
            [ModuleConfig::CONFIG_XML_PATH_CODMETHODS, ScopeInterface::SCOPE_STORE, 'store_one', $storeOneMethods],
            [ModuleConfig::CONFIG_XML_PATH_CODMETHODS, ScopeInterface::SCOPE_STORE, 'store_two', $storeTwoMethods],

        ];

        $this->scopeConfig
            ->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configWriter' => $this->configWriter,
            'scopeConfig' => $this->scopeConfig
        ]);

        $codMethods = $config->getCodPaymentMethods();
        $this->assertInternalType('array', $codMethods);
        $this->assertCount(2, $codMethods);
        $this->assertEquals($defaultMethods, implode(',', $codMethods));

        $codMethods = $config->getCodPaymentMethods('store_one');
        $this->assertInternalType('array', $codMethods);
        $this->assertCount(1, $codMethods);
        $this->assertEquals($storeOneMethods, implode(',', $codMethods));

        $codMethods = $config->getCodPaymentMethods('store_two');
        $this->assertInternalType('array', $codMethods);
        $this->assertCount(0, $codMethods);
    }

    /**
     * @test
     */
    public function canProcessMethodWithValidConditions()
    {
        $defaultShipperCountry  = 'DE';
        $storeOneShipperCountry = 'AT';

        $defaultMethods = 'flatrate_flatrate';
        $storeOneMethods = 'tablerate_bestway';

        $returnValueMap = [
            [ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $defaultShipperCountry],
            [ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, ScopeInterface::SCOPE_STORE, 'store_one', $storeOneShipperCountry],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $defaultMethods],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, ScopeInterface::SCOPE_STORE, 'store_one', $storeOneMethods],
        ];

        $this->scopeConfig
            ->expects($this->exactly(4))
            ->method('getValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configWriter' => $this->configWriter,
            'scopeConfig' => $this->scopeConfig
        ]);

        $this->assertTrue($config->canProcessMethod('flatrate_flatrate'));
        $this->assertTrue($config->canProcessMethod('tablerate_bestway', 'store_one'));
    }

    /**
     * @test
     */
    public function canProcessMethodWithInvalidConditions()
    {
        $validShipperCountry  = 'DE';
        $invalidShipperCountry = 'NZ';

        $validMethod = 'flatrate_flatrate';
        $invalidMethod = 'tablerate_bestway';

        $returnValueMap = [
            [ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $validShipperCountry],
            [ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, ScopeInterface::SCOPE_STORE, 'store_one', $invalidShipperCountry],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, $validMethod],
        ];

        $this->scopeConfig
            ->expects($this->exactly(3))
            ->method('getValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configWriter' => $this->configWriter,
            'scopeConfig' => $this->scopeConfig
        ]);

        $this->assertFalse($config->canProcessMethod($invalidMethod));
        $this->assertFalse($config->canProcessMethod($validMethod, 'store_one'));
    }
}
