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
 * @package   Dhl\Shipping\AutoCreate
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\AutoCreate;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoresConfig;

/**
 * OrderProvider
 *
 * Collect orders for automatic shipment creation and label retrieval.
 *
 * @category Dhl
 * @package  Dhl\Shipping\AutoCreate
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class OrderProvider implements OrderProviderInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var StoresConfig
     */
    private $storesConfig;

    /**
     * @var int[]
     */
    private $orderIds = [];

    /**
     * OrderProvider constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ModuleConfigInterface $moduleConfig
     * @param StoresConfig $storesConfig
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ModuleConfigInterface $moduleConfig,
        StoresConfig $storesConfig
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleConfig = $moduleConfig;
        $this->storesConfig = $storesConfig;
    }

    /**
     * @param int[] $orderIds
     * @return void
     */
    public function setOrderFilter(array $orderIds)
    {
        $this->orderIds = $orderIds;
    }

    /**
     * Load orders applicable to auto creation. Apply data source filters.
     *
     * @return OrderInterface[]
     */
    private function load()
    {
        // shipping method was assigned to DHL Shipping
        $this->searchCriteriaBuilder->addFilter('shipping_method', Carrier::CODE . '_%', 'like');

        // autocreate is enabled for the order's store
        $activeStores = [];
        $activeSettings = $this->storesConfig
            ->getStoresConfigByPath(ModuleConfigInterface::CONFIG_XML_PATH_AUTOCREATE_ENABLED);

        foreach ($activeSettings as $storeId => $isActive) {
            if ($isActive) {
                $activeStores[]= $storeId;
            }
        }
        $this->searchCriteriaBuilder->addFilter('store_id', $activeStores, 'in');

        // order status is allowed for autocreate via module config
        $allowedStatus = $this->moduleConfig->getAutoCreateOrderStatus();
        $this->searchCriteriaBuilder->addFilter('status', $allowedStatus, 'in');

        // exact order IDs
        if (!empty($this->orderIds)) {
            $this->searchCriteriaBuilder->addFilter('entity_id', $this->orderIds, 'in');
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResult = $this->orderRepository->getList($searchCriteria);

        $orders = $searchResult->getItems();
        return $orders;
    }

    /**
     * Apply additional filters to loaded orders.
     *
     * @param OrderInterface[] $orders
     * @return OrderInterface[]
     */
    private function filter(array $orders)
    {
        $canShipFilter = function (OrderInterface $order) {
            /** @var \Magento\Sales\Model\Order $order */
            return $order->canShip();
        };

        $routeFilter = function (OrderInterface $order) {
            /** @var \Magento\Sales\Model\Order $order */
            $destinationCountryId = $order->getShippingAddress()->getCountryId();
            $storeId = $order->getStoreId();
            return $this->moduleConfig->canProcessRoute($destinationCountryId, $storeId);
        };

        $destinationFilter = function (OrderInterface $order) {
            /** @var \Magento\Sales\Model\Order $order */
            $destinationCountryId = $order->getShippingAddress()->getCountryId();
            $storeId = $order->getStoreId();
            return !$this->moduleConfig->isCrossBorderRoute($destinationCountryId, $storeId);
        };

        $orders = array_filter($orders, $canShipFilter);
        $orders = array_filter($orders, $routeFilter);
        $orders = array_filter($orders, $destinationFilter);

        return $orders;
    }

    /**
     * @return OrderInterface[]
     */
    public function getOrders()
    {
        $orders = $this->load();
        $orders = $this->filter($orders);

        return $orders;
    }
}
