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
class ConfigAccessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /** @var  ConfigAccessor */
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
        $this->configModel   = $this->objectManager->create(ConfigAccessor::class);
    }

    /**
     * @test
     */
    public function testSaveAndGet()
    {
        $path  = GlConfig::CONFIG_XML_PATH_AUTH_USERNAME;
        $value = 'myTestValue';
        $this->configModel->saveConfigValue($path, $value, 1);
        $this->assertEquals($value, $this->configModel->getConfigValue($path));
    }
}
