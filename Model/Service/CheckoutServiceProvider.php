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
use Dhl\Shipping\Model\Service\Filter\CheckoutServiceFilter;
use Dhl\Shipping\Model\Service\Option\CompositeOptionProvider;
use Dhl\Shipping\Service\ServiceCompatibilityPool;
use Dhl\Shipping\Service\ServiceHydrator;

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
     * @var ServiceConfig
     */
    private $serviceConfig;

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
     * @var CheckoutServiceFilter
     */
    private $checkoutServiceFilter;

    /**
     * @var CompositeOptionProvider
     */
    private $compositeOptionProvider;

    /**
     * CheckoutServiceProvider constructor.
     *
     * @param ServicePool $servicePool
     * @param ServiceConfig $serviceConfig
     * @param ServiceHydrator $serviceHydrator
     * @param ServiceCompatibilityPool $compatibilityPool
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     * @param CheckoutServiceFilter $checkoutServiceFilter
     * @param CompositeOptionProvider $compositeOptionProvider
     */
    public function __construct(
        ServicePool $servicePool,
        ServiceConfig $serviceConfig,
        ServiceHydrator $serviceHydrator,
        ServiceCompatibilityPool $compatibilityPool,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory,
        CheckoutServiceFilter $checkoutServiceFilter,
        CompositeOptionProvider $compositeOptionProvider
    ) {
        $this->servicePool = $servicePool;
        $this->serviceConfig = $serviceConfig;
        $this->serviceHydrator = $serviceHydrator;
        $this->compatibilityPool = $compatibilityPool;
        $this->serviceSettingsFactory = $serviceSettingsFactory;
        $this->checkoutServiceFilter = $checkoutServiceFilter;
        $this->compositeOptionProvider = $compositeOptionProvider;
    }

    /**
     * @param string $countryId
     * @param string $storeId
     * @return string[][]
     */
    public function getCompatibility($countryId, $storeId)
    {
        return $this->compatibilityPool->getRules($countryId, $storeId);
    }

    /**
     * @param string $countryId
     * @param string $storeId
     * @param string $postalCode
     * @return array
     */
    public function getServices($countryId, $storeId, $postalCode)
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
     * Take a settings array, enrich it with additional data and
     * turn it into ServiceSettingsInterface[].
     *
     * @param string $storeId
     * @param string $postalCode
     * @return ServiceSettingsInterface[]
     */
    private function prepareServiceSettings($storeId, $postalCode)
    {
        $settings = $this->serviceConfig->getServiceSettings($storeId);
        $args = [
            'storeId' => $storeId,
            'postalCode' => $postalCode,
        ];
        $settings = $this->compositeOptionProvider->enhanceServicesWithOptions($settings, $args);

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
    private function filterAvailableServices($countryId, $storeId, $serviceCollection)
    {
        return $this->checkoutServiceFilter->filterServiceCollection($serviceCollection, $storeId, $countryId);
    }

    /**
     * Generates a sorting function for ServiceInterface instances
     *
     * @return \Closure
     */
    private function getSortCallback()
    {
        $sortFn = function (ServiceInterface $serviceA, ServiceInterface $serviceB) {
            if ($serviceA->getSortOrder() === $serviceB->getSortOrder()) {
                return 0;
            }

            return ($serviceA->getSortOrder() < $serviceB->getSortOrder()) ? -1 : 1;
        };

        return $sortFn;
    }
}
