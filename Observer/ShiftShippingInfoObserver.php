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
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use \Dhl\Shipping\Model\Shipping\Carrier;
use \Dhl\Shipping\Model\ShippingInfo\ShippingInfoRepositoryInterface;
use \Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoFactory;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * Shift shipping info from quote address to order address
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShiftShippingInfoObserver implements ObserverInterface
{
    /**
     * @var ShippingInfoRepositoryInterface
     */
    private $quoteInfoRepository;

    /**
     * @var ShippingInfoRepositoryInterface
     */
    private $orderInfoRepository;

    /**
     * @var OrderShippingInfoFactory
     */
    private $orderInfoFactory;

    /**
     * ShiftShippingInfoObserver constructor.
     *
     * @param ShippingInfoRepositoryInterface $quoteInfoRepository
     * @param ShippingInfoRepositoryInterface $orderInfoRepository
     * @param OrderShippingInfoFactory $orderInfoFactory
     */
    public function __construct(
        ShippingInfoRepositoryInterface $quoteInfoRepository,
        ShippingInfoRepositoryInterface $orderInfoRepository,
        OrderShippingInfoFactory $orderInfoFactory
    ) {
        $this->quoteInfoRepository = $quoteInfoRepository;
        $this->orderInfoRepository = $orderInfoRepository;
        $this->orderInfoFactory    = $orderInfoFactory;
    }

    /**
     * When a new order is placed, shift additional DHL shipping information from quote to order
     * from quote address to order address.
     *
     * Event:
     * - sales_model_service_quote_submit_success
     *
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        /** @var \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');

        if ($order->getIsVirtual()) {
            return;
        }

        $shippingMethod = $order->getShippingMethod(true);
        if ($shippingMethod->getData('carrier_code') != Carrier::CODE) {
            return;
        }

        $shippingAddressId = $quote->getShippingAddress()->getId();

        try {
            $infoModel = $this->quoteInfoRepository->getById($shippingAddressId);
            $info = $infoModel->getInfo();
        } catch (NoSuchEntityException $e) {
            $info = '';
        }

        $orderInfo = $this->orderInfoFactory->create(['data' => [
            'address_id' => $order->getShippingAddress()->getId(),
            'info' => $info
        ]]);

        $this->orderInfoRepository->save($orderInfo);
    }
}
