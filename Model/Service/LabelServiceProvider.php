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
use Dhl\Shipping\Api\Data\ServiceSelectionInterface;
use Dhl\Shipping\Api\ServiceSelectionRepositoryInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Service\Filter\RouteFilter;
use Dhl\Shipping\Service\Filter\SelectedFilter;
use Dhl\Shipping\Util\ShippingRoutes\RouteValidatorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Load services for label request
 *
 * @package  Dhl\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class LabelServiceProvider
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
     * @var ServiceSettingsInterfaceFactory
     */
    private $serviceSettingsFactory;

    /**
     * @var ServiceSelectionRepositoryInterface
     */
    private $serviceSelectionRepository;

    /**
     * @var RouteValidatorInterface
     */
    private $routeValidator;

    /**
     * @var ServiceConfig
     */
    private $serviceConfig;

    /**
     * LabelServiceProvider constructor.
     * @param ServicePool $servicePool
     * @param ModuleConfigInterface $config
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     * @param ServiceSelectionRepositoryInterface $serviceSelectionRepository
     * @param RouteValidatorInterface $routeValidator
     * @param ServiceConfig $serviceConfig
     */
    public function __construct(
        ServicePool $servicePool,
        ModuleConfigInterface $config,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory,
        ServiceSelectionRepositoryInterface $serviceSelectionRepository,
        RouteValidatorInterface $routeValidator,
        ServiceConfig $serviceConfig
    ) {
        $this->servicePool = $servicePool;
        $this->config = $config;
        $this->serviceSettingsFactory = $serviceSettingsFactory;
        $this->serviceSelectionRepository = $serviceSelectionRepository;
        $this->routeValidator = $routeValidator;
        $this->serviceConfig = $serviceConfig;
    }

    /**
     * @param ShipmentInterface $shipment
     * @return ServiceInterface[]|ServiceCollection
     */
    public function getServices(array $servicesData, ShipmentInterface $shipment)
    {
        $orderAddressId = $shipment->getOrder()->getShippingAddress()->getId();
        $storeId = $shipment->getStoreId();
        $presets = $this->prepareServiceSettings($orderAddressId, $servicesData, $storeId);

        $serviceCollection = $this->servicePool->getServices($presets);

        $routeFilter = RouteFilter::create(
            $this->routeValidator,
            $this->config->getShipperCountry($shipment->getStoreId()),
            $shipment->getShippingAddress()->getCountryId(),
            $this->config->getEuCountryList($shipment->getStoreId())
        );
        // return only services selected by customer or merchant
        $selectedFilter = SelectedFilter::create();

        $serviceCollection = $serviceCollection->filter($routeFilter)
                                               ->filter($selectedFilter);

        return $serviceCollection;
    }

    /**
     * Take a settings array, enrich it with additional data and
     * turn it into ServiceSettingsInterface[].
     *
     * @param string $storeId
     * @param string[][] $serviceData
     * @return ServiceSettingsInterface[]
     */
    private function prepareServiceSettings($orderAddressId, array $serviceData, $storeId)
    {
        $settings = $this->serviceConfig->getServiceSettings($storeId);
        $orderServices = [];
        try {
            $serviceSelections = $this->serviceSelectionRepository
                ->getByOrderAddressId($orderAddressId)
                ->getItems();

            $serviceSelections = array_filter(
                $serviceSelections,
                function (ServiceSelectionInterface $service) use ($serviceData, $settings) {
                    if ($settings[$service->getServiceCode()][ServiceSettingsInterface::IS_MERCHANT_SERVICE] === true
                        && !array_key_exists($service->getServiceCode(), $serviceData)) {
                        return false;
                    }

                    return true;
                }
            );

            $orderServices = array_reduce(
                $serviceSelections,
                function ($carry, $selection) {
                    /** @var ServiceSelectionInterface $selection */
                    $carry[$selection->getServiceCode()] = $selection->getServiceValue();

                    return $carry;
                },
                []
            );
        } catch (NoSuchEntityException $e) {
            // do nothing
        }

        $serviceData = array_merge($orderServices, $serviceData);
        $inactiveServices = array_diff_key($settings, $serviceData);
        foreach ($serviceData as $serviceCode => $serviceValues) {
            if ($settings[$serviceCode]) {
                $settings[$serviceCode][ServiceSettingsInterface::PROPERTIES] = $serviceValues;
                $settings[$serviceCode][ServiceSettingsInterface::IS_SELECTED] = true;
            }
        }
        foreach (array_keys($inactiveServices) as $serviceCode) {
            $settings[$serviceCode][ServiceSettingsInterface::IS_SELECTED] = false;
        }

        return array_map(
            function ($config) {
                return $this->serviceSettingsFactory->create($config);
            },
            $settings
        );
    }
}
