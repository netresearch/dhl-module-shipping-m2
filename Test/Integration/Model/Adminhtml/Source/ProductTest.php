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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Config;

use Dhl\Shipping\Api\Config\GlConfigInterface;
use \Magento\TestFramework\ObjectManager;

/**
 * LabelSizeTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Dhl\Shipping\Model\Adminhtml\System\Config\Source\Product
     */
    private $model;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->model = $this->objectManager->create(
            \Dhl\Shipping\Model\Adminhtml\System\Config\Source\Product::class
        );
    }

    /**
     * @test
     */
    public function toOptionArray()
    {
        $result = $this->model->toOptionArray();
        $this->assertArrayHasKey('value', $result[0]);
        $this->assertArrayHasKey('label', $result[0]);
        $this->assertEquals(0, $result[0]['value']);
        $this->assertEquals(GlConfigInterface::PRODUCT_PKG, $result[0]['label']);
        $this->assertArrayHasKey('value', $result[1]);
        $this->assertArrayHasKey('label', $result[1]);
        $this->assertEquals(GlConfigInterface::PRODUCT_PPS, $result[1]['label']);
        $this->assertEquals(1, $result[1]['value']);
        $this->assertArrayHasKey('value', $result[2]);
        $this->assertArrayHasKey('label', $result[2]);
        $this->assertEquals(GlConfigInterface::PRODUCT_PPM, $result[2]['label']);
        $this->assertEquals(2, $result[2]['value']);
        $this->assertArrayHasKey('value', $result[3]);
        $this->assertArrayHasKey('label', $result[3]);
        $this->assertEquals(GlConfigInterface::PRODUCT_PLD, $result[3]['label']);
        $this->assertEquals(3, $result[3]['value']);
        $this->assertArrayHasKey('value', $result[4]);
        $this->assertArrayHasKey('label', $result[4]);
        $this->assertEquals(GlConfigInterface::PRODUCT_PKD, $result[4]['label']);
        $this->assertEquals(4, $result[4]['value']);
        $this->assertArrayHasKey('value', $result[5]);
        $this->assertArrayHasKey('label', $result[5]);
        $this->assertEquals(GlConfigInterface::PRODUCT_PLE, $result[5]['label']);
        $this->assertEquals(5, $result[5]['value']);
        $this->assertArrayHasKey('value', $result[6]);
        $this->assertArrayHasKey('label', $result[6]);
        $this->assertEquals(GlConfigInterface::PRODUCT_PLT, $result[6]['label']);
        $this->assertEquals(6, $result[6]['value']);
        $this->assertArrayHasKey('value', $result[7]);
        $this->assertArrayHasKey('label', $result[7]);
        $this->assertEquals(GlConfigInterface::PRODUCT_PKM, $result[7]['label']);
        $this->assertEquals(7, $result[7]['value']);
    }
}
