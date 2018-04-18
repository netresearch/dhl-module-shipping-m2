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
namespace Dhl\Shipping\Model\Config;

use \Dhl\Shipping\Model\Config\ModuleConfig;
use \Magento\TestFramework\ObjectManager;

/**
 * ModuleConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ModuleConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /**
     * Config fixtures are loaded before data fixtures. Config fixtures for
     * non-existent stores will fail. We need to set the stores up first manually.
     * @link http://magento.stackexchange.com/a/93961
     */
    public static function setUpBeforeClass()
    {
        require realpath(TESTS_TEMP_DIR . '/../testsuite/Magento/Store/_files/core_fixturestore_rollback.php');
        require realpath(__DIR__ . '/../../_files/core_second_third_fixturestore_rollback.php');

        require realpath(TESTS_TEMP_DIR . '/../testsuite/Magento/Store/_files/core_fixturestore.php');
        require realpath(TESTS_TEMP_DIR . '/../testsuite/Magento/Store/_files/core_second_third_fixturestore.php');
        parent::setUpBeforeClass();
    }

    /**
     * Delete manually added stores. There is no rollback script for the
     * second and third store (with websites). As long as this does not lead to
     * errors, leave it as is.
     *
     * @see setUpBeforeClass()
     */
    public static function tearDownAfterClass()
    {
        require realpath(TESTS_TEMP_DIR . '/../testsuite/Magento/Store/_files/core_fixturestore_rollback.php');
        require realpath(__DIR__ . '/../../_files/core_second_third_fixturestore_rollback.php');
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     */
    public function sandboxModeDisabled()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->isSandboxModeEnabled());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     */
    public function sandboxModeEnabled()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->isSandboxModeEnabled());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/logging_enabled 0
     */
    public function logIsDisabled()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->isLoggingEnabled());

        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::DEBUG));

        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::ERROR));

        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::WARNING));
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/logging_enabled 1
     * @magentoConfigFixture default/carriers/dhlshipping/log_level 500
     */
    public function logLevelCritical()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::ERROR));
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::WARNING));
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::DEBUG));
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/logging_enabled 1
     * @magentoConfigFixture default/carriers/dhlshipping/log_level 400
     */
    public function logLevelError()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->isLoggingEnabled(\Monolog\Logger::ERROR));
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::WARNING));
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::DEBUG));
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/logging_enabled 1
     * @magentoConfigFixture default/carriers/dhlshipping/log_level 300
     */
    public function logLevelWarning()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->isLoggingEnabled(\Monolog\Logger::ERROR));
        $this->assertTrue($config->isLoggingEnabled(\Monolog\Logger::WARNING));
        $this->assertFalse($config->isLoggingEnabled(\Monolog\Logger::DEBUG));
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/logging_enabled 1
     * @magentoConfigFixture default/carriers/dhlshipping/log_level 100
     */
    public function logLevelDebug()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->isLoggingEnabled(\Monolog\Logger::ERROR));
        $this->assertTrue($config->isLoggingEnabled(\Monolog\Logger::WARNING));
        $this->assertTrue($config->isLoggingEnabled(\Monolog\Logger::DEBUG));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id DE
     * @magentoConfigFixture fixturestore_store shipping/origin/country_id DE
     * @magentoConfigFixture secondstore_store shipping/origin/country_id AT
     * @magentoConfigFixture thirdstore_store shipping/origin/country_id PL
     */
    public function getShipperCountry()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertEquals('DE', $config->getShipperCountry());
        $this->assertEquals('DE', $config->getShipperCountry('fixturestore'));
        $this->assertEquals('AT', $config->getShipperCountry('secondstore'));
        $this->assertEquals('PL', $config->getShipperCountry('thirdstore'));
    }

    /**
     * @test
     * @magentoConfigFixture fixturestore_store general/country/eu_countries FO,BA
     */
    public function getEuCountryList()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);

        $euCountries = $config->getEuCountryList();
        $this->assertInternalType('array', $euCountries);
        $this->assertNotEmpty($euCountries);
        $this->assertContainsOnly('string', $euCountries);

        $euCountries = $config->getEuCountryList('fixturestore');
        $this->assertInternalType('array', $euCountries);
        $this->assertContainsOnly('string', $euCountries);
        $this->assertCount(2, $euCountries);
        $this->assertContains('FO', $euCountries);
        $this->assertContains('BA', $euCountries);
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     * @magentoConfigFixture fixturestore_store carriers/dhlshipping/shipment_dhlmethods
     */
    public function getShippingMethods()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);

        $methods = $config->getShippingMethods();
        $this->assertInternalType('array', $methods);
        $this->assertContainsOnly('string', $methods);
        $this->assertCount(2, $methods);
        $this->assertContains('flatrate_flatrate', $methods);
        $this->assertContains('tablerate_bestway', $methods);

        $methods = $config->getShippingMethods('fixturestore');
        $this->assertInternalType('array', $methods);
        $this->assertCount(0, $methods);
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlcodmethods cashondelivery,nachnahme
     * @magentoConfigFixture fixturestore_store carriers/dhlshipping/shipment_dhlcodmethods
     */
    public function getCodPaymentMethods()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);

        $methods = $config->getCodPaymentMethods();
        $this->assertInternalType('array', $methods);
        $this->assertContainsOnly('string', $methods);
        $this->assertCount(2, $methods);
        $this->assertContains('cashondelivery', $methods);
        $this->assertContains('nachnahme', $methods);

        $methods = $config->getCodPaymentMethods('fixturestore');
        $this->assertInternalType('array', $methods);
        $this->assertCount(0, $methods);
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlcodmethods cashondelivery,nachnahme
     * @magentoConfigFixture fixturestore_store carriers/dhlshipping/shipment_dhlcodmethods
     */
    public function isCodPaymentMethodTrue()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->isCodPaymentMethod('nachnahme'));
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlcodmethods cashondelivery,nachnahme
     * @magentoConfigFixture fixturestore_store carriers/dhlshipping/shipment_dhlcodmethods
     */
    public function isCodPaymentMethodFalse()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->isCodPaymentMethod('cc'));
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/default_shipping_products {"DE":4321,"INTL":4444,"EURO":"1111"}
     */
    public function getDefaultProduct()
    {
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertEquals('4321', $config->getDefaultProduct('DE'));
        $this->assertEquals('4444', $config->getDefaultProduct('MY'));
        $this->assertEquals('1111', $config->getDefaultProduct('AT'));
    }
}
