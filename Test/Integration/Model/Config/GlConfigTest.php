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
 * ConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class GlConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /** @var  GlConfig */
    private $configModel;

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

        $this->configModel   = $this->objectManager->create(GlConfig::class);
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/api_gl_sandbox_endpoint Sandbox
     */
    public function getSandboxApiEndpoint()
    {
        $this->assertEquals('Sandbox', $this->configModel->getApiEndpoint());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/api_gl_endpoint Api
     */
    public function getApiEndpoint()
    {
        $this->assertEquals('Api', $this->configModel->getApiEndpoint());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/api_gl_auth_password password
     */
    public function getAuthPassword()
    {
        $this->assertEquals('password', $this->configModel->getAuthPassword());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/api_gl_sandbox_auth_password sandbox
     */
    public function getAuthPasswordSandboxMode()
    {
        $this->assertEquals('sandbox', $this->configModel->getAuthPassword());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/api_gl_sandbox_auth_username sandbox
     */
    public function getAuthUsernameSandboxMode()
    {
        $this->assertEquals('sandbox', $this->configModel->getAuthUsername());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/api_gl_auth_username username
     */
    public function getAuthUsername()
    {
        $this->assertEquals('username', $this->configModel->getAuthUsername());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/gl_pickup_number 1234
     */
    public function getPickupAccountNumber()
    {
        $this->assertEquals('1234', $this->configModel->getPickupAccountNumber());
    }
}
