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
 * @category  Dhl
 * @package   Dhl\Shipping
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

/**
 * PackagingTemplateObserver
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ChangePackagingTemplateObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * ChangePackagingTemplateObserver constructor.
     * @param \Magento\Framework\Registry $registry
     * @param ModuleConfigInterface $moduleConfig
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        ModuleConfigInterface $moduleConfig
    ) {
        $this->coreRegistry = $registry;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        if ($block instanceof \Magento\Shipping\Block\Adminhtml\Order\Packaging
            && $block->getNameInLayout() === 'shipment_packaging'
        ) {
            /** @var \Magento\Sales\Model\Order\Shipment $currentShipment */
            $currentShipment = $this->coreRegistry->registry('current_shipment');
            /** @var \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order */
            $order = $currentShipment->getOrder();
            $shippingMethod = $order->getShippingMethod(true);
            if ($shippingMethod->getData('carrier_code') === Carrier::CODE) {
                $block->setTemplate('Dhl_Shipping::order/packaging/popup.phtml');
            }
        }
    }
}
