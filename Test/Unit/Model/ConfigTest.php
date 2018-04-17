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

use Dhl\Shipping\Model\Config\ConfigAccessorInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Config\ConfigAccessor;
use Dhl\Shipping\Model\Config\ModuleConfig;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Shipping\Helper\Carrier as CarrierHelper;
use \Magento\Shipping\Model\Config as ShippingConfig;

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
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ConfigAccessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configAccessor;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);
        $this->configAccessor = $this->getMockBuilder(ConfigAccessor::class)
            ->setMethods(['saveConfigValue', 'getConfigValue'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $this->configAccessor
            ->expects($this->any())
            ->method('getConfigValue')
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
            'configAccessor' => $this->configAccessor
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
        $this->configAccessor
            ->expects($this->exactly(2))
            ->method('getConfigValue')
            ->willReturnOnConsecutiveCalls([true, false]);

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configAccessor'  => $this->configAccessor
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
            [ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, null, $defaultCountry],
            [ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, 'store_one', $storeOneCountry],
        ];

        $this->configAccessor
            ->expects($this->exactly(2))
            ->method('getConfigValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configAccessor' => $this->configAccessor,
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
            [CarrierHelper::XML_PATH_EU_COUNTRIES_LIST, null, $defaultList],
            [CarrierHelper::XML_PATH_EU_COUNTRIES_LIST, 'store_one', $storeOneList],
        ];

        $this->configAccessor
            ->expects($this->exactly(2))
            ->method('getConfigValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configAccessor' => $this->configAccessor,
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
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, null, $defaultMethods],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, 'store_one', $storeOneMethods],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, 'store_two', $storeTwoMethods],
        ];

        $this->configAccessor
            ->expects($this->exactly(3))
            ->method('getConfigValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configAccessor' => $this->configAccessor,
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
            [ModuleConfig::CONFIG_XML_PATH_CODMETHODS, null, $defaultMethods],
            [ModuleConfig::CONFIG_XML_PATH_CODMETHODS, 'store_one', $storeOneMethods],
            [ModuleConfig::CONFIG_XML_PATH_CODMETHODS, 'store_two', $storeTwoMethods],

        ];

        $this->configAccessor
            ->expects($this->exactly(3))
            ->method('getConfigValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configAccessor' => $this->configAccessor,
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
    public function canProcessMethod()
    {
        $validMethod = 'flatrate_flatrate';
        $invalidMethod = 'tablerate_bestway';

        $returnValueMap = [
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, null, ''],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, 'store_one', $validMethod],
            [ModuleConfig::CONFIG_XML_PATH_DHLMETHODS, 'store_two', $validMethod],
        ];

        $this->configAccessor
            ->expects($this->exactly(3))
            ->method('getConfigValue')
            ->will($this->returnValueMap($returnValueMap));

        /** @var ModuleConfigInterface $config */
        $config = $this->objectManager->getObject(ModuleConfig::class, [
            'configAccessor' => $this->configAccessor,
        ]);

        // no dhl methods set on global level
        $this->assertFalse($config->canProcessMethod($validMethod));
        // given method does not match configured method
        $this->assertFalse($config->canProcessMethod($invalidMethod, 'store_one'));
        // given method does match configured method
        $this->assertTrue($config->canProcessMethod($validMethod, 'store_two'));
    }

    /**
     * @test
     */
    public function canProcessShipping()
    {
        /** @var ModuleConfigInterface|\PHPUnit_Framework_MockObject_MockObject $config */
        $config = $this->getMockBuilder(ModuleConfig::class)
            ->setMethods(['canProcessMethod', 'canProcessRoute'])
            ->disableOriginalConstructor()
            ->getMock();
        $config
            ->expects($this->any())
            ->method('canProcessMethod')
            ->willReturnOnConsecutiveCalls(true, true, false, false);
        $config
            ->expects($this->any())
            ->method('canProcessRoute')
            ->willReturnOnConsecutiveCalls(true, false, true, false);

        $this->assertTrue($config->canProcessShipping('foo', 'bar'));
        $this->assertFalse($config->canProcessShipping('foo', 'bar'));
        $this->assertFalse($config->canProcessShipping('foo', 'bar'));
        $this->assertFalse($config->canProcessShipping('foo', 'bar'));
    }
}
