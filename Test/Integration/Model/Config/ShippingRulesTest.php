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
 * ShippingRulesTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingRulesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id DE
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessBusinessCustomerShippingDe()
    {
        $destCountryId = 'DE';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id DE
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function cannotProcessBusinessCustomerShippingDe()
    {
        $destCountryId = 'DE';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->canProcessShipping('foo_bar', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id AT
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessBusinessCustomerShippingAt()
    {
        $destCountryId = 'AT';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id AT
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function cannotProcessBusinessCustomerShippingAt()
    {
        $destCountryId = 'AT';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->canProcessShipping('foo_bar', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id AT
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessBusinessCustomerShippingAtToEu()
    {
        $destCountryId = 'BG';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id DE
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessBusinessCustomerShippingDeToEu()
    {
        $destCountryId = 'BG';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id PL
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function cannotProcessBusinessCustomerShippingEuToEu()
    {
        $destCountryId = 'BG';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id DE
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessBusinessCustomerShippingDeToIntl()
    {
        $destCountryId = 'NZ';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id AT
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessBusinessCustomerShippingAtToIntl()
    {
        $destCountryId = 'US';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id US
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessEcommerceShippingUs()
    {
        $destCountryId = 'US';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id US
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function cannotProcessEcommerceShippingUs()
    {
        $destCountryId = 'US';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->canProcessShipping('foo_bar', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id US
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessEcommerceShippingUsToIntl()
    {
        $destCountryId = 'DE';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id CL
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function canProcessEcommerceShippingCl()
    {
        $destCountryId = 'CL';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertTrue($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id CL
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function cannotProcessEcommerceShippingCl()
    {
        $destCountryId = 'CL';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->canProcessShipping('foo_bar', $destCountryId));
    }

    /**
     * @test
     * @magentoConfigFixture default/shipping/origin/country_id CL
     * @magentoConfigFixture default/carriers/dhlshipping/shipment_dhlmethods flatrate_flatrate,tablerate_bestway
     */
    public function cannotProcessEcommerceShippingClToIntl()
    {
        $destCountryId = 'US';
        /** @var ModuleConfig $config */
        $config = $this->objectManager->create(ModuleConfig::class);
        $this->assertFalse($config->canProcessShipping('tablerate_bestway', $destCountryId));
    }
}
