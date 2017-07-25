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
 * PHP version 5
 *
 * @category  Dhl
 * @package   Dhl_Versenden
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Adminhtml\System\Config\Source\Service;

use \Magento\Framework\Option\ArrayInterface;

/**
 * VisualCheckOfAge
 *
 * @category Dhl
 * @package  Dhl_Versenden
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class VisualCheckOfAge implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionsArray = [
            [
                'value' => 0,
                'label' => __('No')
            ],
            [
                'value' => \Dhl\Shipping\Service\VisualCheckOfAge::A16,
                'label' => \Dhl\Shipping\Service\VisualCheckOfAge::A16,
            ],
            [
                'value' => \Dhl\Shipping\Service\VisualCheckOfAge::A18,
                'label' => \Dhl\Shipping\Service\VisualCheckOfAge::A18,
            ]
        ];

        return $optionsArray;
    }
}
