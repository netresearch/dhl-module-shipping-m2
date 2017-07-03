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

use Dhl\Shipping\Api\Config\ModuleConfigInterface as Config;
use Dhl\Shipping\Model\Shipping\Carrier;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\Data\OrderSearchResultInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Store\Model\StoresConfig;

class AutoCreate
{
    /**
     * @var OrderSearchResultInterfaceFactory
     */
    private $orderRepository;
    /**
     * @var LabelGenerator
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
     * AutoCreate constructor.
     * @param LabelGenerator $labelGenerator
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     * @param StoresConfig $storesConfig
     * @internal param OrderRepositoryInterface $orderCollection
     */
    public function __construct(
        LabelGenerator $labelGenerator,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        StoresConfig $storesConfig
    ) {
        $this->labelGenerator = $labelGenerator;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->storesConfig = $storesConfig;
    }

    /**
     * @return mixed[]
     */
    public function run()
    {
        $orders = $this->orderRepository->getList($this->createSearchCriteria());
        $shippedOrders = [];
        foreach ($orders->getItems() as $order) {
            // @TODO ship order
            $shippedOrders[] = $order->getIncrementId();
        }
        return [
            'count' => $orders->getTotalCount(),
            'items' => $shippedOrders
        ];
    }

    /**
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

    private function addShipmentFilter()
    {

    }
}
