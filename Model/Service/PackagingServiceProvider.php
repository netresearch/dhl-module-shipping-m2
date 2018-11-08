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
use Dhl\Shipping\Model\Service\Option\CompositeOptionProvider;
use Dhl\Shipping\Model\Service\Option\OptionProviderInterface;
use Dhl\Shipping\Service\Filter\MerchantSelectionFilter;
use Dhl\Shipping\Service\Filter\RouteFilter;
use Dhl\Shipping\Util\ShippingRoutes\RouteValidatorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Shipment;

/**
 * Load services for packaging popup
 *
 * @package  Dhl\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PackagingServiceProvider
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
     * @var ServiceSelectionRepositoryInterface
     */
    private $serviceSelectionRepo;

    /**
     * @var ServiceSettingsInterfaceFactory
     */
    private $serviceSettingsFactory;

    /**
     * @var CompositeOptionProvider
     */
    private $compositeOptionProvider;

    /**
     * PackagingServiceProvider constructor.
     * @param ServicePool $servicePool
     * @param ModuleConfigInterface $config
     * @param ServiceConfig $serviceConfig
     * @param RouteValidatorInterface $routeValidator
     * @param ServiceSelectionRepositoryInterface $serviceSelectionRepo
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     * @param CompositeOptionProvider $compositeOptionProvider
     */
    public function __construct(
        ServicePool $servicePool,
        ModuleConfigInterface $config,
        ServiceConfig $serviceConfig,
        RouteValidatorInterface $routeValidator,
        ServiceSelectionRepositoryInterface $serviceSelectionRepo,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory,
        CompositeOptionProvider $compositeOptionProvider
    ) {
        $this->servicePool = $servicePool;
        $this->config = $config;
        $this->serviceConfig = $serviceConfig;
        $this->routeValidator = $routeValidator;
        $this->serviceSelectionRepo = $serviceSelectionRepo;
        $this->serviceSettingsFactory = $serviceSettingsFactory;
        $this->compositeOptionProvider = $compositeOptionProvider;
    }

    /**
     * @param ShipmentInterface|Shipment $shipment
     * @return ServiceCollection|ServiceInterface[]
     */
    public function getServices(ShipmentInterface $shipment)
    {
        $orderAddressId = $shipment->getOrder()->getShippingAddress()->getId();

        $presets = $this->prepareServiceSettings($orderAddressId, $shipment->getStoreId());

        $serviceCollection = $this->servicePool->getServices($presets);

        // show services available for merchants
        $adminFilter = MerchantSelectionFilter::create();
        $routeFilter = RouteFilter::create(
            $this->routeValidator,
            $this->config->getShipperCountry($shipment->getStoreId()),
            $shipment->getShippingAddress()->getCountryId(),
            $this->config->getEuCountryList($shipment->getStoreId())
        );
        $serviceCollection = $serviceCollection
            ->filter($adminFilter)
            ->filter($routeFilter);

        /**
         * @param ServiceInterface $a
         * @param ServiceInterface $b
         * @return int
         */
        $sortFunction = function ($a, $b) {
            if ($a->getSortOrder() === $b->getSortOrder()) {
                return 0;
            }

            return $a->getSortOrder() > $b->getSortOrder() ? 1 : -1;
        };

        return $serviceCollection->sort($sortFunction);
    }

    /**
     * Take a settings array, enrich it with additional data and
     * turn it into ServiceSettingsInterface[].
     *
     * @param string $storeId
     * @param string $orderAddressId
     * @return ServiceSettingsInterface[]
     */
    private function prepareServiceSettings(string $orderAddressId, string $storeId)
    {
        $settings = $this->serviceConfig->getServiceSettings($storeId);

        /**
         * Add service values from serviceSelection objects
         */
        try {
            /** @var ServiceSelectionInterface[] $serviceSelections */
            $serviceSelections = $this->serviceSelectionRepo
                ->getByOrderAddressId($orderAddressId)
                ->getItems();

            foreach ($serviceSelections as $selection) {
                if ($settings[$selection->getServiceCode()]) {
                    $settings[$selection->getServiceCode(
                    )][ServiceSettingsInterface::PROPERTIES] = $selection->getServiceValue();
                    $settings[$selection->getServiceCode()][ServiceSettingsInterface::IS_SELECTED] = true;
                }
                // add selected options to service
                $settings = $this->compositeOptionProvider
                    ->enhanceServicesWithOptions(
                        $settings,
                        [OptionProviderInterface::ARGUMENT_SELECTION => $selection]
                    );
            }
        } catch (NoSuchEntityException $e) {
            // do nothing
        }

        return array_map(
            function ($config) {
                return $this->serviceSettingsFactory->create($config);
            },
            $settings
        );
    }
}
