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

use \Magento\TestFramework\ObjectManager;

/**
 * BcsConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class BcsConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var BcsConfig
     */
    private $config;

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
        $this->config = $this->objectManager->create(BcsConfig::class);
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_sandbox_api_endpoint Sandbox
     */
    public function getSandboxApiEndpoint()
    {
        $this->assertEquals('Sandbox', $this->config->getApiEndpoint());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_api_endpoint Api
     */
    public function getApiEndpoint()
    {
        $this->assertNull($this->config->getApiEndpoint());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_api_auth_password password
     */
    public function getAuthPassword()
    {
        $this->assertEquals('password', $this->config->getAuthPassword());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_sandbox_api_auth_password sandbox
     */
    public function getAuthPasswordSandboxMode()
    {
        $this->assertEquals('sandbox', $this->config->getAuthPassword());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_sandbox_api_auth_username sandbox
     */
    public function getAuthUsernameSandboxMode()
    {
        $this->assertEquals('sandbox', $this->config->getAuthUsername());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_api_auth_username username
     */
    public function getAuthUsername()
    {
        $this->assertEquals('username', $this->config->getAuthUsername());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_sandbox_account_user username
     */
    public function getAccountUserSandBoxMode()
    {
        $this->assertEquals('username', $this->config->getAccountUser());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_account_user username
     */
    public function getAccountUser()
    {
        $this->assertEquals('username', $this->config->getAccountUser());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_account_user U53Rn4m3
     */
    public function getAccountUserWithMixedCase()
    {
        $this->assertEquals('u53rn4m3', $this->config->getAccountUser());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_sandbox_account_signature signature
     */
    public function getAccountSignatureSandBoxMode()
    {
        $this->assertEquals('signature', $this->config->getAccountSignature());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_account_signature signature
     */
    public function getAccountSignature()
    {
        $this->assertEquals('signature', $this->config->getAccountSignature());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_sandbox_account_ekp account_ekp
     */
    public function getAccountEkpSandBoxMode()
    {
        $this->assertEquals('account_ekp', $this->config->getAccountEkp());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_account_ekp account_ekp
     */
    public function getAccountEkp()
    {
        $this->assertEquals('account_ekp', $this->config->getAccountEkp());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 1
     */
    public function getAccountParticipationSandBoxMode()
    {
        $this->assertEquals('02', $this->config->getAccountParticipation('83'));
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/sandbox_mode 0
     */
    public function getAccountParticipation()
    {
        $this->assertEquals('02', $this->config->getAccountParticipation('83'));
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_bankdata_account_owner owner
     */
    public function getBankDataAccountOwner()
    {
        $this->assertEquals('owner', $this->config->getBankDataAccountOwner());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_bankdata_bankname bank
     */
    public function getBankDataBankName()
    {
        $this->assertEquals('bank', $this->config->getBankDataBankName());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_bankdata_iban iban
     */
    public function getBankDataIban()
    {
        $this->assertEquals('iban', $this->config->getBankDataIban());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_bankdata_bic bic
     */
    public function getBankDataBic()
    {
        $this->assertEquals('bic', $this->config->getBankDataBic());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_bankdata_note1 note1
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_bankdata_note2 note2
     */
    public function getBankDataNote()
    {
        $result = $this->config->getBankDataNote();
        $this->assertTrue(is_array($result));
        $this->assertEquals(2, count($result));
        $this->assertEquals('note1', $result[0]);
        $this->assertEquals('note2', $result[1]);
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_bankdata_account_reference reference
     */
    public function getBankDataAccountReference()
    {
        $this->assertEquals('reference', $this->config->getBankDataAccountReference());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_shipper_contact_company_addition company
     */
    public function getShipperCompanyAddition()
    {
        $this->assertEquals('company', $this->config->getShipperCompanyAddition());
    }

    /**
     * @test
     */
    public function deprecatedGettersReturnNull()
    {
        $this->assertNull($this->config->getShipperCompany());
        $this->assertNull($this->config->getShipperCity());
        $this->assertNull($this->config->getShipperCountryISOCode());
        $this->assertNull($this->config->getShipperEmail());
        $this->assertNull($this->config->getShipperName());
        $this->assertNull($this->config->getShipperPostalCode());
        $this->assertNull($this->config->getShipperRegion());
        $this->assertNull($this->config->getShipperStreet());
        $this->assertNull($this->config->getShipperStreetNumber());
    }

    /**
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/bcs_shipper_contact_dispatchinfo info
     */
    public function getDispatchingInformation()
    {
        $this->assertEquals('info', $this->config->getDispatchingInformation());
    }
}
