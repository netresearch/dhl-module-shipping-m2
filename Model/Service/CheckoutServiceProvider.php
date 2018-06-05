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
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Service\Filter\CustomerSelectionFilter;
use Dhl\Shipping\Service\Filter\RouteFilter;
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
     * CheckoutServiceProvider constructor.
     *
     * @param ServicePool $servicePool
     * @param ModuleConfigInterface $config
     * @param RouteValidatorInterface $routeValidator
     * @param ServiceHydrator $serviceHydrator
     */
    public function __construct(
        ServicePool $servicePool,
        ModuleConfigInterface $config,
        RouteValidatorInterface $routeValidator,
        ServiceHydrator $serviceHydrator
    ) {
        $this->servicePool = $servicePool;
        $this->config = $config;
        $this->routeValidator = $routeValidator;
        $this->serviceHydrator = $serviceHydrator;
    }

    /**
     * @param string $countryId
     * @param string $storeId
     * @return mixed[]
     */
    public function getServices($countryId, $storeId)
    {
        $presets = $this->config->getServiceSettings($storeId);
        // todo(nr): merge config defaults with values from session or shipping info structure?

        $serviceCollection = $this->servicePool->getServices($presets);

        // show only services available for customers
        $checkoutFilter = CustomerSelectionFilter::create();
        $routeFilter = RouteFilter::create(
            $this->routeValidator,
            $this->config->getShipperCountry($storeId),
            $countryId,
            $this->config->getEuCountryList($storeId)
        );

        /** @var ServiceCollection $serviceCollection */
        $serviceCollection = $serviceCollection->filter($checkoutFilter);
        $serviceCollection = $serviceCollection->filter($routeFilter);

        $callback = $this->getSortCallback();
        $serviceCollection = $serviceCollection->sort($callback);

        $services = $serviceCollection->map([$this->serviceHydrator, 'extract']);

        return $services;
    }

    /**
     * @return \Closure
     */
    private function getSortCallback()
    {
        $sortFn = function (ServiceInterface $serviceA, ServiceInterface $serviceB) {
            if ($serviceA->getSortOrder() == $serviceB->getSortOrder()) {
                return 0;
            }
            return ($serviceA->getSortOrder() < $serviceB->getSortOrder()) ? -1 : 1;
        };

        return $sortFn;
    }
}
