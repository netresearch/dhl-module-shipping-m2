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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Api\LabelStatusRepositoryInterface;
use Dhl\Shipping\Api\Data\LabelStatusInterfaceFactory;
use Dhl\Shipping\Model\Label\LabelStatus;
use Dhl\Shipping\Model\SalesOrderGrid\OrderGridUpdater;
use Dhl\Shipping\Model\Shipping\Carrier;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * UpdateLabelStatusObserver
 *
 * @category Dhl
 * @package  Dhl\Shipping\Observer
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UpdateLabelStatusObserver implements ObserverInterface
{
    /**
     * @var LabelStatusInterfaceFactory
     */
    private $labelStatusFactory;

    /**
     * @var LabelStatusRepositoryInterface
     */
    private $labelStatusRepository;

    /**
     * @var OrderGridUpdater
     */
    private $orderGridUpdater;

    /**
     * UpdateLabelStatusObserver constructor.
     *
     * @param LabelStatusInterfaceFactory $statusFactory
     * @param LabelStatusRepositoryInterface $labelStatusRepo
     * @param OrderGridUpdater $orderGridUpdater
     */
    public function __construct(
        LabelStatusInterfaceFactory $statusFactory,
        LabelStatusRepositoryInterface $labelStatusRepo,
        OrderGridUpdater $orderGridUpdater
    ) {
        $this->labelStatusFactory = $statusFactory;
        $this->labelStatusRepository = $labelStatusRepo;
        $this->orderGridUpdater = $orderGridUpdater;
    }

    /**
     * When an order is shippable and can be shipped with DHL Shipping,
     * this will check for the order's label status,
     * create a new label status if neccessary,
     * and update the order grid.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        $errors = $observer->getEvent()->getData('errors');
        $carrier = $order->getShippingMethod(true)->getData('carrier_code');

        if ($order->getIsVirtual() || $carrier !== Carrier::CODE) {
            return;
        }
        $orderId = $order->getId();
        $isPartial = $order->canShip();
        $labelStatus = $this->labelStatusRepository->getByOrderId($orderId);

        if ($labelStatus === null) {
            $labelStatus = $this->labelStatusFactory->create();
            $labelStatus->setOrderId($orderId);
            $labelStatus->setStatusCode(LabelStatus::CODE_PENDING);
        }

        if (isset($errors)) {
            $labelStatus->setStatusCode(LabelStatus::CODE_FAILED);
        } elseif ($isPartial) {
            $labelStatus->setStatusCode(LabelStatus::CODE_PENDING);
        } else {
            $labelStatus->setStatusCode(LabelStatus::CODE_PROCESSED);
        }
        $this->labelStatusRepository->save($labelStatus);
        $this->orderGridUpdater->update($orderId);
    }
}
