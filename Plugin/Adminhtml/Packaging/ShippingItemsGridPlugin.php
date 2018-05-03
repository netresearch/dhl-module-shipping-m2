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
 * Max Melzer <max.melzer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Plugin\Adminhtml\Packaging;

use Dhl\Shipping\Block\Adminhtml\Order\Shipment\Packaging\Grid;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\GetShippingItemsGrid;
use Magento\Framework\App\ResponseInterface;

/**
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingItemsGridPlugin
{
    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * ShippingItemsGridPlugin constructor.
     *
     * @param ModuleConfigInterface $moduleConfig
     * @param \Magento\Framework\Registry $coreRegistry
     * @param LayoutInterface $layout
     */
    public function __construct(
        ModuleConfigInterface $moduleConfig,
        \Magento\Framework\Registry $coreRegistry,
        LayoutInterface $layout
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->coreRegistry = $coreRegistry;
        $this->layout = $layout;
    }

    /**
     * Overwrite the ShippingItemsGrid block for DHL cross border shipments.
     *
     * @param GetShippingItemsGrid $subject
     * @param ResponseInterface $result
     * @return ResponseInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterExecute(
        GetShippingItemsGrid $subject,
        $result
    ) {
        /** @var Shipment $shipment */
        $shipment = $this->coreRegistry->registry('current_shipment');
        $carrier = $shipment->getOrder()->getShippingMethod(true)->getCarrierCode();
        $isCrossBorder = $this->moduleConfig->isCrossBorderRoute(
            $shipment->getShippingAddress()->getCountryId(),
            $shipment->getStoreId()
        );
        if ($carrier !== Carrier::CODE || !$isCrossBorder) {
            return $result;
        }
        $index = $subject->getRequest()->getParam('index');
        /** @var Grid $block */
        $block = $this->layout->createBlock(Grid::class);
        $block->setIndex($index);
        $html = $block->toHtml();

        return $subject->getResponse()->setBody($html);
    }
}
