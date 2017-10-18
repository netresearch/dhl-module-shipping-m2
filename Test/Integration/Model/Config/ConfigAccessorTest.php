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
use \Magento\Framework\App\Config\Storage\Writer as ConfigWriter;

/**
 * ConfigAccessorTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ConfigAccessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * Config fixtures are loaded before data fixtures. Config fixtures for
     * non-existent stores will fail. We need to set the stores up first manually.
     * @link http://magento.stackexchange.com/a/93961
     */
    public static function setUpBeforeClass()
    {
        require realpath(TESTS_TEMP_DIR . '/../testsuite/Magento/Store/_files/core_fixturestore_rollback.php');
        require realpath(TESTS_TEMP_DIR . '/../testsuite/Magento/Store/_files/core_fixturestore.php');
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
        parent::tearDownAfterClass();
    }

    /**
     * Assert config value is passed through from accessor to writer
     *
     * @test
     */
    public function saveConfig()
    {
        $path  = GlConfig::CONFIG_XML_PATH_AUTH_USERNAME;
        $value = 'myTestValue';

        $writerMock = $this->getMockBuilder(ConfigWriter::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();
        $writerMock
            ->expects($this->once())
            ->method('save')
            ->with($path, $value);

        /** @var ConfigAccessor $configAccessor */
        $configAccessor = $this->objectManager->create(ConfigAccessor::class, ['configWriter' => $writerMock]);
        $configAccessor->saveConfigValue($path, $value, 1);
    }

    /**
     * Assert config value is read from correct scope
     *
     * @test
     * @magentoConfigFixture default/carriers/dhlshipping/title CarrierFoo
     * @magentoConfigFixture fixturestore_store carriers/dhlshipping/title CarrierBar
     */
    public function getConfig()
    {
        $storeCode = 'fixturestore';
        $store = $this->objectManager->create(\Magento\Store\Model\Store::class)->load($storeCode);

        /** @var ConfigAccessor $configAccessor */
        $configAccessor = $this->objectManager->create(ConfigAccessor::class);

        $defaultValue = $configAccessor->getConfigValue(ModuleConfig::CONFIG_XML_PATH_TITLE);
        $storeValue = $configAccessor->getConfigValue(ModuleConfig::CONFIG_XML_PATH_TITLE, $store->getId());

        $this->assertEquals('CarrierFoo', $defaultValue);
        $this->assertEquals('CarrierBar', $storeValue);
    }
}
