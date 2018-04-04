<?php
/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 03.04.18
 * Time: 15:45
 */

namespace Dhl\Shipping\Model;

use Dhl\Shipping\AutoCreate\LabelGeneratorInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Model\Order;

class CreateShipment
{
    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var LabelGeneratorInterface
     */
    private $labelGenerator;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;
    /**
     * @var ShipmentSender
     */
    private $shipmentSender;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    public function __construct(
        ShipmentFactory $shipmentFactory,
        TransactionFactory $transactionFactory,
        TransactionRepositoryInterface $transactionRepository,
        ShipmentSender $shipmentSender,
        LabelGeneratorInterface $labelGenerator,
        ModuleConfigInterface $moduleConfig
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
        $this->moduleConfig = $moduleConfig;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentSender = $shipmentSender;
    }

    /**
     * @param Order $order
     */
    public function create(Order $order)
    {
        $shippingMethod = $order->getShippingMethod( true);

        if ($shippingMethod->getData('carrier_code') !== Carrier::CODE) {
            throw new LocalizedException(__('Not a DHL order'));
        }
        if (!$order->canShip()) {
            throw new LocalizedException(__('Order cannot be shipped'));
        }

        $isCrossBorder = $this->moduleConfig->isCrossBorderRoute(
            $order->getShippingAddress()->getCountryId(),
            $order->getStoreId()
        );
        if ($isCrossBorder) {
            throw new LocalizedException(__('Crossboarder shipments cannot be created automatically'));
        }

        $items = [];
        foreach ($order->getAllItems() as $orderItem) {
            $items[$orderItem->getItemId()] = $orderItem->getQtyOrdered();
        }

        /** @var Order\Shipment $shipment */
        $shipment = $this->shipmentFactory->create($order, $items);
        $shipment->addComment('Shipment automatically created by Dhl Shipping.');
        $shipment->register();

        $this->labelGenerator->create($shipment);
        $order->setIsInProcess(true);
        $transaction = $this->transactionFactory->create();
        $transaction->addObject($shipment);
        $transaction->addObject($order);
        $transaction->save();

        if ($this->moduleConfig->getAutoCreateNotifyCustomer($order->getStoreId())) {
            $this->shipmentSender->send($shipment);
        }
    }
}
