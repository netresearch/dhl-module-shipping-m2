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
 * @package   Dhl\Shipping\Block
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\Sales;

use Dhl\Shipping\Model\Total;
use Magento\Directory\Model\Currency;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\Totals as TotalsBlock;

/**
 * Admin Sales Order Totals
 *
 * @package  Dhl\Shipping\Block
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var Total
     */
    private $shippingModel;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Totals constructor.
     *
     * @param Context $context
     * @param Currency $currency
     * @param Total $shippingModel
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Currency $currency,
        Total $shippingModel,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->currency = $currency;
        $this->shippingModel = $shippingModel;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }

    /**
     * Add service charge totals to admin sales order.
     *
     * @return $this
     */
    public function initTotals()
    {
        /** @var TotalsBlock $parentBlock */
        $parentBlock = $this->getParentBlock();
        $order = $parentBlock->getOrder();
        $shipping = (float) $order->getData(Total::SERVICE_CHARGE_FIELD_NAME);

        if (!$shipping) {
            return $this;
        }

        $total = $this->dataObjectFactory->create([
            'data' => [
                'code' => $this->shippingModel->getCode(),
                'value' => $shipping,
                'label' => $this->shippingModel->getLabel()
            ]
        ]);

        $parentBlock->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
