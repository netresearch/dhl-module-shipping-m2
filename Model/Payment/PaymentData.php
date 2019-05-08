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
 * @package   Dhl\Shipping
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Payment;

use Magento\Payment\Helper\Data;
use Magento\Payment\Model\MethodInterface;

/**
 * Class PaymentData
 *
 * @package Dhl\Shipping\Model
 */
class PaymentData extends Data
{
    /**
     * Set active flag based on current store configuration, not config defaults.
     *
     * The core payment methods source model is broken again. It displays ony methods enabled in config defaults.
     * The broken method is very long so we fix the issue by setting the active flag before the broken method is called.
     *
     * @see \Magento\Payment\Model\Config\Source\Allmethods::toOptionArray
     * @see \Magento\Payment\Helper\Data::getPaymentMethodList
     * @see \Magento\Payment\Helper\Data::getPaymentMethods
     *
     * @return mixed[]
     */
    public function getPaymentMethods()
    {
        // display all methods or only methods activated in store configuration
        $allMethods = true;

        /** @var MethodInterface[] $activeMethods */
        $activeMethods = $this->_paymentConfig->getActiveMethods();

        $methods = parent::getPaymentMethods();

        foreach ($methods as $key => &$methodData) {
            if ($allMethods) {
                $methodData['active'] = true;
            } else {
                $methodData['active'] = isset($activeMethods[$key]) && $activeMethods[$key]->isActive();
            }
        }

        return $methods;
    }
}
