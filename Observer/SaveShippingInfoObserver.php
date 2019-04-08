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
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Api\OrderAddressExtensionRepositoryInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use Dhl\Shipping\Model\ShippingInfo\AbstractAddressExtension;
use Dhl\Shipping\Model\ShippingInfo\OrderAddressExtensionFactory;
use Dhl\Shipping\Model\ShippingInfoBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Persist Shipping Info Observer.
 *
 * @package  Dhl\Shipping\Observer
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class SaveShippingInfoObserver implements ObserverInterface
{
    /**
     * @var OrderAddressExtensionRepositoryInterface
     */
    private $orderAddressExtensionRepository;

    /**
     * @var OrderAddressExtensionFactory
     */
    private $addressExtensionFactory;

    /**
     * @var ShippingInfoBuilder
     */
    private $shippingInfoBuilder;

    /**
     * ShiftShippingInfoObserver constructor.
     *
     * @param OrderAddressExtensionRepositoryInterface $orderAddressExtensionRepository
     * @param OrderAddressExtensionFactory $addressExtensionFactory
     * @param ShippingInfoBuilder $shippingInfoBuilder
     */
    public function __construct(
        OrderAddressExtensionRepositoryInterface $orderAddressExtensionRepository,
        OrderAddressExtensionFactory $addressExtensionFactory,
        ShippingInfoBuilder $shippingInfoBuilder
    ) {
        $this->orderAddressExtensionRepository = $orderAddressExtensionRepository;
        $this->addressExtensionFactory = $addressExtensionFactory;
        $this->shippingInfoBuilder = $shippingInfoBuilder;
    }

    /**
     * When a new order is placed, persist additional DHL shipping information.
     *
     * Event:
     * - sales_model_service_quote_submit_success
     *
     * @param Observer $observer
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');

        if ($order->getIsVirtual()) {
            return;
        }

        $carrierCode = strtok((string) $order->getShippingMethod(), '_');
        if ($carrierCode !== Carrier::CODE) {
            return;
        }

        $shippingAddressId = $order->getShippingAddress()->getId();
        $this->shippingInfoBuilder->setShippingAddress($order->getShippingAddress());
        $shippingInfo = $this->shippingInfoBuilder->create();

        $addressExtension = $this->addressExtensionFactory->create(['data' => [
            AbstractAddressExtension::ADDRESS_ID => $shippingAddressId,
            AbstractAddressExtension::SHIPPING_INFO => $shippingInfo
        ]]);

        $this->orderAddressExtensionRepository->save($addressExtension);
    }
}
