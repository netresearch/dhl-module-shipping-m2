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
 * @package   Dhl\Shipping
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model;

use Dhl\Shipping\AutoCreate\LabelGeneratorInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ShipmentFactory;

/**
 * CreateShipment
 *
 * @package  Dhl\Shipping
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
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
     * @var ShipmentSender
     */
    private $shipmentSender;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * CreateShipment constructor.
     *
     * @param ShipmentFactory $shipmentFactory
     * @param TransactionFactory $transactionFactory
     * @param ShipmentSender $shipmentSender
     * @param LabelGeneratorInterface $labelGenerator
     * @param ModuleConfigInterface $moduleConfig
     */
    public function __construct(
        ShipmentFactory $shipmentFactory,
        TransactionFactory $transactionFactory,
        ShipmentSender $shipmentSender,
        LabelGeneratorInterface $labelGenerator,
        ModuleConfigInterface $moduleConfig
    ) {
        $this->shipmentFactory = $shipmentFactory;
        $this->transactionFactory = $transactionFactory;
        $this->moduleConfig = $moduleConfig;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentSender = $shipmentSender;
    }

    /**
     * @param Order $order
     * @param bool $isCron
     * @return Order\Shipment
     * @throws LocalizedException
     * @throws \Exception
     */
    public function create(Order $order, $isCron = false)
    {
        $shippingMethod = $order->getShippingMethod(true);

        if ($shippingMethod->getData('carrier_code') !== Carrier::CODE) {
            throw new LocalizedException(__('Order cannot be shipped with DHL'));
        }
        if (!$order->canShip()) {
            throw new LocalizedException(__('Order cannot be shipped'));
        }

        /** @var array $items   [itemId => itemQtyOrdered] */
        $items = [];
        foreach ($order->getAllItems() as $orderItem) {
            $items[$orderItem->getItemId()] = $orderItem->getQtyOrdered();
        }

        /** @var Order\Shipment $shipment */
        $shipment = $this->shipmentFactory->create($order, $items);
        if ($isCron) {
            $shipment->addComment('Shipment automatically created by DHL Shipping.');
        }
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

        return $shipment;
    }
}
