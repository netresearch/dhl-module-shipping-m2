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

use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterfaceFactory;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Service\Filter\CustomerSelectionFilter;
use Dhl\Shipping\Service\Filter\RouteFilter;
use Dhl\Shipping\Service\ServiceCompatibilityPool;
use Dhl\Shipping\Service\ServiceHydrator;
use Dhl\Shipping\Util\ShippingRoutes\RouteValidatorInterface;

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
     * CheckoutServiceProvider constructor.
     *
     * @param ServicePool $servicePool
     * @param ModuleConfigInterface $config
     * @param RouteValidatorInterface $routeValidator
     * @param ServiceHydrator $serviceHydrator
     * @param ServiceCompatibilityPool $compatibilityPool
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     */
    public function __construct(
        ServicePool $servicePool,
        ModuleConfigInterface $config,
        RouteValidatorInterface $routeValidator,
        ServiceHydrator $serviceHydrator,
        ServiceCompatibilityPool $compatibilityPool,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory
    )
    {
        $this->servicePool = $servicePool;
        $this->config = $config;
        $this->routeValidator = $routeValidator;
        $this->serviceHydrator = $serviceHydrator;
        $this->compatibilityPool = $compatibilityPool;
        $this->serviceSettingsFactory = $serviceSettingsFactory;
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
     * @param int|null $orderID
     * @return array
     */
    public function getServices($countryId, $storeId, $orderID = null): array
    {
        $presets = $this->prepareServiceSettings($storeId);

        $serviceCollection = $this->servicePool->getServices($presets);

        $serviceCollection = $this->filterAvailableServices($countryId, $storeId, $serviceCollection);

        $serviceCollection = $serviceCollection->sort($this->getSortCallback());

        $services = $serviceCollection->map(function ($service) {
            return $this->serviceHydrator->extract($service);
        });

        return $services;
    }

    /**
     * Take a settings array, enrich it with additional data and
     * turn it into ServiceSettingsInterface[].
     *
     * @param string $storeId
     * @return ServiceSettingsInterface[]
     */
    private function prepareServiceSettings(string $storeId): array
    {
        $settings = $this->config->getServiceSettings($storeId);

        return array_map(
            function ($config) {
                return $this->serviceSettingsFactory->create($config);
            },
            $settings
        );
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
        $serviceCollection = $serviceCollection->filter($checkoutFilter);
        $serviceCollection = $serviceCollection->filter($routeFilter);

        return $serviceCollection;
    }
}
