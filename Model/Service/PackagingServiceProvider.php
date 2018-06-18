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
use Dhl\Shipping\Api\Data\ServiceSelectionInterface;
use Dhl\Shipping\Api\ServiceSelectionRepositoryInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
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
     * @var RouteValidatorInterface
     */
    private $routeValidator;

    /**
     * @var ServiceSelectionRepositoryInterface
     */
    private $serviceSelectionRepo;

    /**
     * PackagingServiceProvider constructor.
     *
     * @param ServicePool $servicePool
     * @param ModuleConfigInterface $config
     * @param RouteValidatorInterface $routeValidator
     * @param ServiceSelectionRepositoryInterface $serviceSelectionRepo
     */
    public function __construct(ServicePool $servicePool, ModuleConfigInterface $config, RouteValidatorInterface $routeValidator, ServiceSelectionRepositoryInterface $serviceSelectionRepo)
    {
        $this->servicePool = $servicePool;
        $this->config = $config;
        $this->routeValidator = $routeValidator;
        $this->serviceSelectionRepo = $serviceSelectionRepo;
    }

    /**
     * @param ShipmentInterface|\Magento\Sales\Model\Order\Shipment $shipment
     * @return ServiceCollection|ServiceInterface[]
     */
    public function getServices(ShipmentInterface $shipment)
    {
        $presets = $this->config->getServiceSettings($shipment->getStoreId());

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

        $serviceCollection = $this->augmentWithServiceSelection($serviceCollection, $shipment);

        return $serviceCollection;
    }

    /**
     * Put values from matching ServiceSelection into Service objects
     *
     * @param ServiceCollection $serviceCollection
     * @param ShipmentInterface $shipment
     * @return ServiceCollection
     */
    private function augmentWithServiceSelection(
        ServiceCollection $serviceCollection,
        ShipmentInterface $shipment
    ): ServiceCollection
    {
        try {
            /** @var ServiceSelectionInterface[] $serviceSelections */
            $serviceSelections = $this->serviceSelectionRepo
                ->getByOrderAddressId($shipment->getOrder()->getShippingAddressId())
                ->getItems();

            foreach ($serviceSelections as $selection) {
                if ($serviceCollection->offsetExists($selection->getServiceCode())) {
                    /** @var ServiceInterface $service */
                    $service = $serviceCollection->offsetGet($selection->getServiceCode());
                    $service->setInputValues($selection->getServiceValue());
                    $service->setSelected(true);
                }
            }
        } catch (NoSuchEntityException $e) {
            // do nothing
        }

        return $serviceCollection;
    }
}
