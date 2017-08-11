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
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Cron;

use Dhl\Shipping\AutoCreate\LabelGeneratorInterface;
use Dhl\Shipping\AutoCreate\OrderProviderInterface;
use Magento\Cron\Model\Schedule;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\ShipmentFactory;

/**
 * Cron entry point for automatic shipment creation and label retrieval
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AutoCreate
{
    const MESSAGE_TEMPLATE = '%d shipments were created. %d shipments could not be created.';

    /**
     * @var OrderProviderInterface
     */
    private $orderProvider;

    /**
     * @var LabelGeneratorInterface
     */
    private $labelGenerator;

    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * AutoCreate constructor.
     * @param OrderProviderInterface $orderProvider
     * @param LabelGeneratorInterface $labelGenerator
     * @param ShipmentFactory $shipmentFactory
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        OrderProviderInterface $orderProvider,
        LabelGeneratorInterface $labelGenerator,
        ShipmentFactory $shipmentFactory,
        TransactionFactory $transactionFactory
    ) {
        $this->orderProvider = $orderProvider;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentFactory = $shipmentFactory;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * Queries for orders that could be automatically shipped and processes them via the corresponding API
     *
     * @param Schedule $schedule
     * @return void
     */
    public function run(Schedule $schedule)
    {
        $failedShipments = [];
        $createdShipments = [];

        $orders = $this->orderProvider->getOrders();

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($orders as $order) {
            try {
                /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                $shipment = $this->shipmentFactory->create($order);
                $shipment->addComment('Shipment automatically created by Dhl Shipping.');
                $shipment->register();

                $this->labelGenerator->create($shipment);

                $shipment->getOrder()->setIsInProcess(true);
                $transaction = $this->transactionFactory->create();
                $transaction->addObject($shipment);
                $transaction->addObject($shipment->getOrder());
                $transaction->save();

                $createdShipments[$order->getIncrementId()] = $shipment->getIncrementId();
            } catch (LocalizedException $exception) {
                $failedShipments[$order->getIncrementId()] = $exception->getMessage();
            }
        }

        $scheduleMessage = sprintf(self::MESSAGE_TEMPLATE, count($createdShipments), count($failedShipments));
        $schedule->setMessages($scheduleMessage);
    }
}
