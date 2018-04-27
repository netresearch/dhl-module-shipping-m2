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
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Cron;

use Dhl\Shipping\AutoCreate\OrderProviderInterface;
use Dhl\Shipping\Model\CreateShipment;
use Magento\Cron\Model\Schedule;

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
     * @var CreateShipment
     */
    private $createShipment;

    /**
     * AutoCreate constructor.
     *
     * @param OrderProviderInterface $orderProvider
     * @param CreateShipment $createShipment
     */
    public function __construct(
        OrderProviderInterface $orderProvider,
        CreateShipment $createShipment
    ) {
        $this->orderProvider = $orderProvider;
        $this->createShipment = $createShipment;
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

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($this->orderProvider->getOrders() as $order) {
            try {
                $shipment = $this->createShipment->create($order, true);
                $createdShipments[$order->getIncrementId()] = $shipment->getIncrementId();
            } catch (\Exception $exception) {
                $failedShipments[$order->getIncrementId()] = $exception->getMessage();
            }
        }

        $scheduleMessage = sprintf(self::MESSAGE_TEMPLATE, count($createdShipments), count($failedShipments));
        $schedule->setMessages($scheduleMessage);
    }
}
