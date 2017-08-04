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

use Dhl\Shipping\Model\Config\ModuleConfigInterface as Config;
use Dhl\Shipping\Model\Shipping\Carrier;
use Dhl\Shipping\Cron\AutoCreate\LabelGeneratorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface as Order;
use Magento\Sales\Api\Data\ShipmentInterface as Shipment;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Store\Model\StoresConfig;

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
     * @var OrderSearchResultInterfaceFactory
     */
    private $orderRepository;

    /**
     * @var LabelGeneratorInterface
     */
    private $labelGenerator;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoresConfig
     */
    private $storesConfig;

    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /**
     * AutoCreate constructor.
     * @param LabelGeneratorInterface $labelGenerator
     * @param ShipmentFactory $shipmentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     * @param StoresConfig $storesConfig
     */
    public function __construct(
        LabelGeneratorInterface $labelGenerator,
        ShipmentFactory $shipmentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        StoresConfig $storesConfig
    ) {
        $this->labelGenerator = $labelGenerator;
        $this->shipmentFactory = $shipmentFactory;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->storesConfig = $storesConfig;
    }

    /**
     * Queries for orders that could be automatically shipped and processes them via the corresponding API
     *
     * @return mixed[]
     * @throws LocalizedException
     */
    public function run()
    {
        $orders = $this->orderRepository->getList($this->createSearchCriteria());
        $shipments = [];
        $shippedOrders = [];
        /** @var Order $order */
        foreach ($orders->getItems() as $order) {
            $canProcessRoute = $this->config->canProcessRoute(
                $order->getShippingAddress()
                      ->getCountryId()
            );

            $isCrossBorderRoute = $this->config->isCrossBorderRoute(
                $order->getShippingAddress()->getCountryId(),
                $order->getStoreId()
            );
            if (!$order->canShip() || !$canProcessRoute || $isCrossBorderRoute) {
                continue;
            }

            try {
                $shipments[] = $this->createAndSubmitShipment($order);
                $shippedOrders[] = $order->getIncrementId();
            } catch (LocalizedException $exception) {
                $message = $exception->getMessage();
                throw new LocalizedException(
                    __(
                        'Could not create shipment for OrderId %1. Error: %2',
                        [
                            $order->getIncrementId(),
                            $message
                        ]
                    )
                );
            }
        }
        return [
            'count' => $orders->getTotalCount(),
            'orderIds' => $shippedOrders,
            'shipments' => $shipments
        ];
    }

    /**
     * Collect all SearchCriteria
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function createSearchCriteria()
    {
        $this->addOrderStatusFilter();
        $this->addCarrierFilter();
        $this->addStoreIdFilter();
        return $this->searchCriteriaBuilder->create();
    }

    /**
     * Restrict search to orders shipped with Dhl Shipping carrier
     *
     */
    private function addCarrierFilter()
    {
        $this->searchCriteriaBuilder->addFilter(
            'shipping_method',
            Carrier::CODE . '_%',
            'like'
        );
    }

    /**
     * Restrict search to orders to statuses defined in config
     *
     */
    private function addOrderStatusFilter()
    {
        $this->searchCriteriaBuilder->addFilter(
            'status',
            $this->config->getCronOrderStatuses(),
            'in'
        );
    }

    /**
     * Restrict search to stores that don't have autocreation disabled
     */
    private function addStoreIdFilter()
    {
        // find stores where autocreate is DISabled
        $inActiveStores = array_filter(
            $this->storesConfig->getStoresConfigByPath(Config::CONFIG_XML_PATH_CRON_ENABLED),
            function ($value) {
                return !(bool)$value;
            }
        );
        if (!empty($inActiveStores)) {
            $this->searchCriteriaBuilder->addFilter(
                'store_id',
                array_keys($inActiveStores),
                'not_in'
            );
        }
    }

    /**
     * @param $order
     *
     * @return Shipment
     */
    private function createAndSubmitShipment($order)
    {
        /** @var Shipment $shipment */
        $shipment = $this->shipmentFactory->create($order);
        $shipment->addComment('Shipment automatically created by Dhl Shipping.');
        $shipment->register();
        $shipment->setPackages(
            [
                [
                    'params' => [
                        'container' => $this->config->getDefaultProduct($shipment->getStoreId()),
                        'weight' => $shipment->getTotalWeight(),
                    ],
                    'items' => $shipment->getAllItems()
                ]
            ]
        );
        $this->labelGenerator->create($shipment);
        return $shipment;
    }
}
