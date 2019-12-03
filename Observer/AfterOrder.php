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
 * @package   Dhl\Shipping\Observer
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Model\Total;
use Magento\Framework\Event\ObserverInterface;

/**
 * AfterOrder Observer.
 *
 * @package  Dhl\Shipping\Observer
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AfterOrder implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * Event:
     * - sales_model_service_quote_submit_before
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getData('quote');
        $shipping = (float) $quote->getData(Total::SERVICE_CHARGE_FIELD_NAME);
        $shippingBase = (float) $quote->getData(Total::SERVICE_CHARGE_BASE_FIELD_NAME);
        if (!$shipping || !$shippingBase) {
            return $this;
        }

        $order = $observer->getData('order');
        $order->setData(Total::SERVICE_CHARGE_FIELD_NAME, $shipping);
        $order->setData(Total::SERVICE_CHARGE_BASE_FIELD_NAME, $shippingBase);

        return $this;
    }
}
