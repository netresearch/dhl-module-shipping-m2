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
 * @package   Dhl\Shipping\Model
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Service;

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterfaceFactory;
use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Service\Filter\InStockFilter;
use Dhl\Shipping\Model\Service\Option\CompositeOptionProvider;
use Dhl\Shipping\Service\Filter\CustomerSelectionFilter;
use Dhl\Shipping\Service\Filter\RouteFilter;
use Dhl\Shipping\Service\ServiceCompatibilityPool;
use Dhl\Shipping\Service\ServiceHydrator;
use Dhl\Shipping\Util\ShippingRoutes\RouteValidatorInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Load services for display in checkout
 *
 * @package  Dhl\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CheckoutServiceProvider
{
    /**
     * @var ServicePool
     */
    private $servicePool;

    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var ServiceConfig
     */
    private $serviceConfig;

    /**
     * @var RouteValidatorInterface
     */
    private $routeValidator;

    /**
     * @var ServiceHydrator
     */
    private $serviceHydrator;

    /**
     * @var ServiceCompatibilityPool
     */
    private $compatibilityPool;

    /**
     * @var ServiceSettingsInterfaceFactory
     */
    private $serviceSettingsFactory;

    /**
     * @var SessionManagerInterface|CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var CompositeOptionProvider
     */
    private $compositeOptionProvider;

    /**
     * CheckoutServiceProvider constructor.
     * @param ServicePool $servicePool
     * @param ModuleConfigInterface $config
     * @param ServiceConfig $serviceConfig
     * @param RouteValidatorInterface $routeValidator
     * @param ServiceHydrator $serviceHydrator
     * @param ServiceCompatibilityPool $compatibilityPool
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     * @param CheckoutSession|SessionManagerInterface $checkoutSession
     * @param StockRegistryInterface $stockRegistry
     * @param CompositeOptionProvider $compositeOptionProvider
     */
    public function __construct(
        ServicePool $servicePool,
        ModuleConfigInterface $config,
        ServiceConfig $serviceConfig,
        RouteValidatorInterface $routeValidator,
        ServiceHydrator $serviceHydrator,
        ServiceCompatibilityPool $compatibilityPool,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory,
        $checkoutSession,
        StockRegistryInterface $stockRegistry,
        CompositeOptionProvider $compositeOptionProvider
    ){
        $this->servicePool = $servicePool;
        $this->config = $config;
        $this->serviceConfig = $serviceConfig;
        $this->routeValidator = $routeValidator;
        $this->serviceHydrator = $serviceHydrator;
        $this->compatibilityPool = $compatibilityPool;
        $this->serviceSettingsFactory = $serviceSettingsFactory;
        $this->checkoutSession = $checkoutSession;
        $this->stockRegistry = $stockRegistry;
        $this->compositeOptionProvider = $compositeOptionProvider;
    }


    /**
     * @param string $countryId
     * @param string $storeId
     * @return string[][]
     */
    public function getCompatibility($countryId, $storeId): array
    {
        return $this->compatibilityPool->getRules($countryId, $storeId);
    }

    /**
     * @param string $countryId
     * @param string $storeId
     * @param string $postalCode
     * @return array
     */
    public function getServices($countryId, $storeId, $postalCode): array
    {
        $presets = $this->prepareServiceSettings($storeId, $postalCode);
        $serviceCollection = $this->servicePool->getServices($presets);
        $serviceCollection = $this->filterAvailableServices($countryId, $storeId, $serviceCollection);
        $serviceCollection = $serviceCollection->sort($this->getSortCallback());

        $services = $serviceCollection->map(
            function ($service) {
                return $this->serviceHydrator->extract($service);
            }
        );

        return $services;
    }

    /**
     * @return \Closure
     */
    private function getSortCallback(): callable
    {
        $sortFn = function (ServiceInterface $serviceA, ServiceInterface $serviceB) {
            if ($serviceA->getSortOrder() === $serviceB->getSortOrder()) {
                return 0;
            }

            return ($serviceA->getSortOrder() < $serviceB->getSortOrder()) ? -1 : 1;
        };

        return $sortFn;
    }

    /**
     * Take a settings array, enrich it with additional data and
     * turn it into ServiceSettingsInterface[].
     *
     * @param string $storeId
     * @param string $postalCode
     * @return ServiceSettingsInterface[]
     */
    private function prepareServiceSettings(string $storeId, string $postalCode): array
    {
        $settings = $this->serviceConfig->getServiceSettings($storeId);
        $this->compositeOptionProvider->enhanceServicesWithOptions($settings, ['postalCode' => $postalCode]);


        return array_map(
            function ($config) {
                return $this->serviceSettingsFactory->create($config);
            },
            $settings
        );
    }

    /**
     * @param string $countryId
     * @param string $storeId
     * @param ServiceCollection $serviceCollection
     * @return ServiceCollection
     */
    private function filterAvailableServices($countryId, $storeId, $serviceCollection): ServiceCollection
    {
        $checkoutFilter = CustomerSelectionFilter::create();
        $routeFilter = RouteFilter::create(
            $this->routeValidator,
            $this->config->getShipperCountry($storeId),
            $countryId,
            $this->config->getEuCountryList($storeId)
        );

        $cartItems = $this->checkoutSession->getQuote()->getItems();
        $inStockFilter = InStockFilter::create($cartItems, $this->stockRegistry);

        $serviceCollection = $serviceCollection->filter($checkoutFilter);
        $serviceCollection = $serviceCollection->filter($routeFilter);
        $serviceCollection = $serviceCollection->filter($inStockFilter);

        return $serviceCollection;
    }
}
