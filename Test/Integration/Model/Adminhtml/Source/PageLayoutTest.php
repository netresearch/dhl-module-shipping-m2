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
class PageLayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Dhl\Shipping\Model\Adminhtml\System\Config\Source\PageLayout
     */
    private $model;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->model = $this->objectManager->create(
            \Dhl\Shipping\Model\Adminhtml\System\Config\Source\PageLayout::class
        );
    }

    /**
     * @test
     */
    public function toOptionArray()
    {
        $result = $this->model->toOptionArray();
        $this->assertArrayHasKey('value' ,$result[0]);
        $this->assertArrayHasKey('label' ,$result[0]);
        $this->assertEquals(0, $result[0]['value']);
        $this->assertEquals(GlConfigInterface::PAGE_LAYOUT_1X1, $result[0]['label']);
        $this->assertArrayHasKey('value' ,$result[1]);
        $this->assertArrayHasKey('label' ,$result[1]);
        $this->assertEquals(GlConfigInterface::PAGE_LAYOUT_4X1, $result[1]['label']);
        $this->assertEquals(1, $result[1]['value']);
    }
}
