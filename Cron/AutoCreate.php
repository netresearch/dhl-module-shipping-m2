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
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
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
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var Phrase[]
     */
    private $errors = [];

    /**
     * AutoCreate constructor.
     * @param OrderProviderInterface $orderProvider
     * @param LabelGeneratorInterface $labelGenerator
     * @param ShipmentFactory $shipmentFactory
     * @param ModuleConfigInterface $moduleConfig
     */
    public function __construct(
        OrderProviderInterface $orderProvider,
        LabelGeneratorInterface $labelGenerator,
        ShipmentFactory $shipmentFactory,
        ModuleConfigInterface $moduleConfig
    ) {
        $this->orderProvider = $orderProvider;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentFactory = $shipmentFactory;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Queries for orders that could be automatically shipped and processes them via the corresponding API
     *
     * @return mixed[]
     */
    public function run(Schedule $schedule)
    {
        $createdShipments = [];
        $storeProducts = [];
        $orders = $this->orderProvider->getOrders();

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($orders as $order) {
            $storeId = $order->getStoreId();
            if (!isset($storeProducts[$storeId])) {
                $storeProducts[$storeId] = $this->moduleConfig->getDefaultProduct($storeId);
            }

            try {
                /** @var \Magento\Sales\Model\Order\Shipment $shipment */
                $shipment = $this->shipmentFactory->create($order);
                $shipment->addComment('Shipment automatically created by Dhl Shipping.');
                $shipment->register();

                $package = [
                    'params' => [
                        'container' => $storeProducts[$storeId],
                        'weight' => $shipment->getTotalWeight(),
                    ],
                    'items' => $shipment->getAllItems()
                ];
                $shipment->setPackages([$package]);

                $this->labelGenerator->create($shipment);
                $createdShipments[$order->getIncrementId()] = $shipment;
            } catch (LocalizedException $exception) {
                $messageTemplate = 'Could not create shipment for OrderId %1. Error: %2';
                $this->errors[] = __($messageTemplate, $order->getIncrementId(), $exception->getMessage());
            }
        }

        $schedule->setMessages('foo');
        return [
            'count' => count($orders),
            'orderIds' => array_keys($createdShipments),
            'shipments' => array_values($createdShipments),
        ];
    }

    /**
     * @return Phrase[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
