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
namespace Dhl\Shipping\Block\Adminhtml\Sales\Order\Creditmemo;

use Dhl\Shipping\Model\Total;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals as CreditmemoTotals;
use Magento\Framework\View\Element\Template\Context;

/**
 * Creditmemo Totals
 *
 * @package  Dhl\Shipping\Block
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Totals extends \Magento\Framework\View\Element\Template
{
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
     * @param Total $shipping
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Total $shipping,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
        $this->shippingModel = $shipping;
    }

    /**
     * Add service charge totals to creditmemo.
     *
     * @return $this
     */
    public function initTotals()
    {
        /** @var CreditmemoTotals $parentBlock */
        $parentBlock = $this->getParentBlock();
        $creditmemo = $parentBlock->getCreditmemo();

        $shipping = (float) $creditmemo->getData(Total::SERVICE_CHARGE_FIELD_NAME);
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
