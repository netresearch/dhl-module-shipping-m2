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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Dhl\Shipping\Model\Total as Shipping;

/**
 * Invoice Total.
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Total extends AbstractTotal
{
    /**
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $invoice->setData(Shipping::SERVICE_CHARGE_FIELD_NAME, 0);
        $invoice->setData(Shipping::SERVICE_CHARGE_BASE_FIELD_NAME, 0);
        $amount = $invoice->getOrder()->getData(Shipping::SERVICE_CHARGE_FIELD_NAME);
        $invoice->setData(Shipping::SERVICE_CHARGE_FIELD_NAME, $amount);
        $amount = $invoice->getOrder()->getData(Shipping::SERVICE_CHARGE_BASE_FIELD_NAME);
        $invoice->setData(Shipping::SERVICE_CHARGE_BASE_FIELD_NAME, $amount);
        $invoice->setGrandTotal(
            $invoice->getGrandTotal() + $invoice->getData(Shipping::SERVICE_CHARGE_FIELD_NAME)
        );
        $invoice->setBaseGrandTotal(
            $invoice->getBaseGrandTotal() + $invoice->getData(Shipping::SERVICE_CHARGE_FIELD_NAME)
        );

        return $this;
    }
}
