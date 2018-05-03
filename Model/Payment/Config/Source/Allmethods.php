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
 * @package   Dhl\Shipping\Model
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Payment\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Payment\Helper\Data;

/**
 * Payment methods source model.
 *
 * A core bug prevents payment methods from being listed in groups.
 * As a workaround, display them as flat list. As soon as the issue is resolved,
 * revert back to original class.
 *
 * @see \Magento\Payment\Model\Config\Source\Allmethods
 * @link https://github.com/magento/magento2/issues/13460
 * @link https://bugs.nr/DHLVM2-197
 *
 * @package  Dhl\Shipping\Model
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Allmethods implements OptionSourceInterface
{
    /**
     * @var Data
     */
    private $paymentData;

    /**
     * @param Data $paymentData
     */
    public function __construct(Data $paymentData)
    {
        $this->paymentData = $paymentData;
    }

    /**
     * @return string[][]
     */
    public function toOptionArray()
    {
        return $this->paymentData->getPaymentMethodList(true, true, false);
    }
}
