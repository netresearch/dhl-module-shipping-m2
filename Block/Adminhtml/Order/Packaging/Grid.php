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
 * @author    Rico Sonntag <rico.sonntag@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\Order\Packaging;

use Dhl\Shipping\Traits\EscapeHtmlAttrTrait;
use Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid as MagentoGrid;

/**
 * Class Grid
 *
 * @category Dhl
 * @author   Rico Sonntag <rico.sonntag@netresearch.de>
 */
class Grid extends MagentoGrid
{
    use EscapeHtmlAttrTrait;
}
