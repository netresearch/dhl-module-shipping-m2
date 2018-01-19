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
 * @package   Dhl\Shipping\Model
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Adminhtml\System\Config\Source\Service;

use Magento\Framework\Option\ArrayInterface;

/**
 * VisualCheckOfAge
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class VisualCheckOfAge implements ArrayInterface
{
    const OPTION_A16 = 'A16';
    const OPTION_A18 = 'A18';

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
                'value' => self::OPTION_A16,
                'label' => self::OPTION_A16,
            ],
            [
                'value' => self::OPTION_A18,
                'label' => self::OPTION_A18,
            ]
        ];

        return $optionsArray;
    }
}
