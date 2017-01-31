<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Model\Adminhtml\System\Config\Source;

use Dhl\Versenden\Bcs\Api\Product;

/**
 * Procedure
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Procedure implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];

        $options = $this->toArray();
        foreach ($options as $value => $label) {
            $optionArray[] = ['value' => $value, 'label' => $label];
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            Product::PROCEDURE_PAKET_NATIONAL          => __('DHL Paket: V01PAK'),
            Product::PROCEDURE_WELTPAKET               => __('DHL Paket International: V53WPAK'),
            Product::PROCEDURE_PAKET_AUSTRIA           => __('DHL Paket Austria: V86PARCEL'),
            Product::PROCEDURE_PAKET_CONNECT           => __('DHL PAKET Connect: V87PARCEL'),
            Product::PROCEDURE_PAKET_INTERNATIONAL     => __('DHL PAKET International: V82PARCEL'),
            Product::PROCEDURE_RETURNSHIPMENT_NATIONAL => __('Retoure DHL Paket: V01PAK'),
            Product::PROCEDURE_RETURNSHIPMENT_AUSTRIA  => __('Retoure DHL Paket Austria: V86PARCEL'),
            Product::PROCEDURE_RETURNSHIPMENT_CONNECT  => __('Retoure DHL Paket Connect: V87PARCEL'),
        ];
    }
}
