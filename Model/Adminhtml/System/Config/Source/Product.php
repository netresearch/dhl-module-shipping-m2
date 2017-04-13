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
 * @package   Dhl\Shipping
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Adminhtml\System\Config\Source;

use Dhl\Shipping\Api\Config\GlConfigInterface;

/**
 * Product
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Product
{
    /**
     * Options getter
     *
     * @return mixed
     */
    public function toOptionArray()
    {
        $optionArray = [];

        $options = $this->toArray();
        foreach ($options as $value => $label) {
            $optionArray[]= ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    /**
     * Get options
     *
     * @return mixed
     */
    public function toArray()
    {
        return [
            0 => GlConfigInterface::PRODUCT_PKG,
            1 => GlConfigInterface::PRODUCT_PPS,
            2 => GlConfigInterface::PRODUCT_PPM,
            3 => GlConfigInterface::PRODUCT_PLD,
            4 => GlConfigInterface::PRODUCT_PKD,
            5 => GlConfigInterface::PRODUCT_PLE,
            6 => GlConfigInterface::PRODUCT_PLT,
            7 => GlConfigInterface::PRODUCT_PKM
         ];
    }
}
