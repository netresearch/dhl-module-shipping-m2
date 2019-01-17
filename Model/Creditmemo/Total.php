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
namespace Dhl\Shipping\Model\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Dhl\Shipping\Model\Total as Shipping;

/**
 * Creditmemo Total.
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Total extends AbstractTotal
{

    /**
     * @param Creditmemo $creditmemo
     * @return $this|AbstractTotal
     */
    public function collect(Creditmemo $creditmemo)
    {
        $creditmemo->setData(Shipping::SERVICE_CHARGE_FIELD_NAME, 0);
        $creditmemo->setData(Shipping::SERVICE_CHARGE_BASE_FIELD_NAME, 0);

        $amount = $creditmemo->getOrder()->getData(Shipping::SERVICE_CHARGE_FIELD_NAME);
        $creditmemo->setData(Shipping::SERVICE_CHARGE_FIELD_NAME, $amount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);

        $baseAmount = $creditmemo->getOrder()->getData(Shipping::SERVICE_CHARGE_BASE_FIELD_NAME);
        $creditmemo->setData(Shipping::SERVICE_CHARGE_BASE_FIELD_NAME, $baseAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseAmount);

        return $this;
    }
}
