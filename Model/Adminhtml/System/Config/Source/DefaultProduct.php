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

use Dhl\Shipping\Api\Util\ShippingProductsInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Product
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class DefaultProduct implements ArrayInterface
{
    private $shippingProducts;

    /**
     * DefaultProduct constructor.
     * @param ShippingProductsInterface $shippingProducts
     */
    public function __construct(ShippingProductsInterface $shippingProducts)
    {
        $this->shippingProducts = $shippingProducts;
    }

    /**
     * Options getter
     *
     * @return string[][]
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
     * @return string[]
     */
    public function toArray()
    {
        $codes = $this->shippingProducts->getAllCodes();
        $names = array_map(function ($code) {
            return $this->shippingProducts->getProductName($code);
        }, $codes);

        return array_combine($codes, $names);
    }
}
