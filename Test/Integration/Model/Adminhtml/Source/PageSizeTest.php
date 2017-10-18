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
namespace Dhl\Shipping\Model\Adminhtml\System\Config\Source;

use Dhl\Shipping\Config\GlConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * LabelSizeTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PageSizeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function toOptionArray()
    {
        $validOptions = [
            GlConfigInterface::PAGE_SIZE_A4,
            GlConfigInterface::PAGE_SIZE_400X400,
            GlConfigInterface::PAGE_SIZE_400X600,
        ];

        $source = Bootstrap::getObjectManager()->create(PageSize::class);
        $options = $source->toOptionArray();

        $this->assertCount(count($validOptions), $options);
        foreach ($options as $sourceOption) {
            $this->assertArrayHasKey('value', $sourceOption);
            $this->assertArrayHasKey('label', $sourceOption);

            $this->assertContains($sourceOption['value'], $validOptions);
            $this->assertContains($sourceOption['label'], $validOptions);
        }
    }
}
