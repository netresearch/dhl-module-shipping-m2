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
use Dhl\Shipping\Api\ServiceSelectionRepositoryInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Service\Filter\SelectedFilter;
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
     * LabelServiceProvider constructor.
     * @param ServicePool $servicePool
     * @param ModuleConfigInterface $config
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     * @param ServiceSelectionRepositoryInterface $serviceSelectionRepository
     */
    public function __construct(
        ServicePool $servicePool,
        ModuleConfigInterface $config,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory,
        ServiceSelectionRepositoryInterface $serviceSelectionRepository
    ) {
        $this->servicePool = $servicePool;
        $this->config = $config;
        $this->serviceSettingsFactory = $serviceSettingsFactory;
        $this->serviceSelectionRepository = $serviceSelectionRepository;
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

        // return only services selected by customer or merchant
        $selectedFilter = SelectedFilter::create();
        $serviceCollection = $serviceCollection->filter($selectedFilter);

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
    private function prepareServiceSettings($orderAddressId, array $serviceData, string $storeId): array
    {
        $settings = $this->config->getServiceSettings($storeId);
        try {
            $serviceSelections = $this->serviceSelectionRepository
                ->getByOrderAddressId($orderAddressId)
                ->getItems();

            foreach ($serviceSelections as $selection) {
                if ($settings[$selection->getServiceCode()]) {
                    $settings[$selection->getServiceCode(
                    )][ServiceSettingsInterface::PROPERTIES] = $selection->getServiceValue();
                    $settings[$selection->getServiceCode()][ServiceSettingsInterface::IS_SELECTED] = true;
                }
            }
        } catch (NoSuchEntityException $e) {
            // do nothing
        }
        foreach ($serviceData as $serviceCode => $serviceValues) {
            if ($settings[$serviceCode]) {
                $settings[$serviceCode][ServiceSettingsInterface::PROPERTIES] = $serviceValues;
                $settings[$serviceCode][ServiceSettingsInterface::IS_SELECTED] = true;
            }
        }

        return array_map(
            function ($config) {
                return $this->serviceSettingsFactory->create($config);
            },
            $settings
        );
    }
}
