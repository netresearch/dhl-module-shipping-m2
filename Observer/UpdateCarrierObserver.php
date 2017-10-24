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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * UpdateCarrierObserver
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UpdateCarrierObserver implements ObserverInterface
{
    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * UpdateCarrierObserver constructor.
     * @param ModuleConfigInterface $config
     */
    public function __construct(ModuleConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * When a new order is placed, set the DHL Shipping carrier if applicable.
     * Event:
     * - sales_order_place_after
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        if ($order->getIsVirtual()) {
            return;
        }

        $shippingMethod = $order->getShippingMethod();
        $recipientCountry = $order->getShippingAddress()->getCountryId();

        if ($this->config->canProcessShipping($shippingMethod, $recipientCountry, $order->getStoreId())) {
            $parts          = explode('_', $shippingMethod);
            $parts[0]       = Carrier::CODE;
            $shippingMethod = implode('_', $parts);
            $order->setData('shipping_method', $shippingMethod);
        }
    }
}
