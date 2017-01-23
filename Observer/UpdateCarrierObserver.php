<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Observer;

use Dhl\Versenden\Api\ConfigInterface;
use Dhl\Versenden\Model\Shipping\Carrier;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * UpdateCarrierObserver
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UpdateCarrierObserver implements ObserverInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * UpdateCarrierObserver constructor.
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * When a new order is placed, set the DHL Versenden carrier if applicable.
     * Event:
     * - sales_order_place_after
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order */
        $order          = $observer->getEvent()->getData('order');
        $shippingMethod = $order->getShippingMethod();

        if ($this->config->canProcessMethod($shippingMethod, $order->getStoreId())) {
            $parts          = explode('_', $shippingMethod);
            $parts[0]       = Carrier::CODE;
            $shippingMethod = implode('_', $parts);
            $order->setData('shipping_method', $shippingMethod);
        }
    }
}
