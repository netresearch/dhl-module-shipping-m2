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
 * @package   Dhl\Shipping\Model
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * DGCategory
 *
 * @package  Dhl\Shipping\Model
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class DGCategory extends AbstractSource
{
    const CODE = 'dhl_dangerous_goods_category';

    /**
     * @return mixed[]
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                [
                    'label' => 'none',
                    'value' => ''
                ],
                [
                    'label' => '01 - Lithium Metal Contained in Equipment',
                    'value' => '01'
                ],
                [
                    'label' => '04 - Lithium-Ion Contained in Equipment',
                    'value' => '04'
                ]
            ];
        }
        return $this->_options;
    }
}
