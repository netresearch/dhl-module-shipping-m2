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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use Dhl\Shipping\ViewModel\Packaging\ShippingItems;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * ReplaceShippingItemsGridObserver
 *
 * @package Dhl\Shipping\Observer
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.netresearch.de/
 */
class ChangePackagingItemsTemplateObserver implements ObserverInterface
{
    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ShippingItems
     */
    private $viewModel;

    /**
     * ChangePackagingItemsTemplateObserver constructor.
     * @param ModuleConfigInterface $config
     * @param Registry $registry
     * @param ShippingItems $viewModel
     */
    public function __construct(
        ModuleConfigInterface $config,
        Registry $registry,
        ShippingItems $viewModel
    ) {
        $this->config = $config;
        $this->registry = $registry;
        $this->viewModel = $viewModel;
    }

    /**
     * Set appropriate template to packaging items grid block and attach view model.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $block = $observer->getData('block');
        if (!$block instanceof \Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid) {
            return;
        }

        /** @var ShipmentInterface|\Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->registry->registry('current_shipment');
        $order = $shipment->getOrder();

        $shippingMethod = $order->getShippingMethod();
        $pattern = sprintf('/^%s_/', Carrier::CODE);
        if (!preg_match($pattern, $shippingMethod)) {
            // do not replace template for other carriers
            return;
        }

        $isCrossBorder = $this->config->isCrossBorderRoute(
            $shipment->getShippingAddress()->getCountryId(),
            $shipment->getStoreId()
        );
        if (!$isCrossBorder) {
            // do not replace template for domestic shipments
            return;
        }

        $originCountryId = $this->config->getShipperCountry($shipment->getStoreId());
        if (in_array($originCountryId, ['DE', 'AT'])) {
            $block->setTemplate('Dhl_Shipping::order/packaging/grid_bcs.phtml');
        } else {
            $block->setTemplate('Dhl_Shipping::order/packaging/grid_gl.phtml');
        }

        $block->setData('viewModel', $this->viewModel);
    }
}
