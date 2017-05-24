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

use Dhl\Shipping\Api\Util\GlShippingProductsInterface;

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
     * @return mixed[]
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
     * @return mixed[]
     */
    public function toArray()
    {
        return [
            GlShippingProductsInterface::CODE_APAC_PPS => 'GM Packet Plus Standard (PPS)',
            GlShippingProductsInterface::CODE_APAC_PPM => 'GM Packet Plus Priority Manifest (PPM)',
            GlShippingProductsInterface::CODE_APAC_PKD => 'GM Packet Standard (PKD)',
            GlShippingProductsInterface::CODE_APAC_PKG => 'GM Packet Economy (PKG)',
            GlShippingProductsInterface::CODE_APAC_PKM => 'GM Packet Priority Manifest (PKM)',
            GlShippingProductsInterface::CODE_APAC_PLT => 'Parcel International Direct (PLT)',
            GlShippingProductsInterface::CODE_APAC_PLD => 'Parcel International Standard (PLD)',
            GlShippingProductsInterface::CODE_APAC_PLE => 'Parcel International Direct Expedited (PLE)',
            GlShippingProductsInterface::CODE_APAC_AP7 => 'GM Paket Pus Manifest Clearance (AP7)',
            GlShippingProductsInterface::CODE_APAC_PDP => 'GM Parcel Direct Plus (PDP)',
            GlShippingProductsInterface::CODE_APAC_PDO => 'PDO',
            GlShippingProductsInterface::CODE_AMER_BMY => 'DHL GM Business Priority (BMY)',
            GlShippingProductsInterface::CODE_AMER_BMD => 'DHL GM Business Standard (BMD)',
            GlShippingProductsInterface::CODE_AMER_PKT => 'DHL GM Packet Plus (PKT)',
            GlShippingProductsInterface::CODE_AMER_PLX => 'DHL GM Parcel Direct Express (PLX)',
            GlShippingProductsInterface::CODE_AMER_PLY => 'DHL Parcel International Standard (PLY)',
            GlShippingProductsInterface::CODE_AMER_PLT => 'DHL Parcel International Direct (PLT)',
            GlShippingProductsInterface::CODE_AMER_PID => 'DHL Parcel International Direct Standard (PID)',
            GlShippingProductsInterface::CODE_AMER_PIY => 'DHL Parcel International Direct Priority (PIY)',
        ];
    }
}
