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

use Dhl\Shipping\Api\ServiceProviderInterface;
use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;

/**
 * Central hub for all shipping services, e.g.
 * - Additional Insurance
 * - Cash on Delivery
 *
 * Each connected webservice can inject its own services.
 *
 * @package  Dhl\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServicePool
{
    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var ServiceProviderInterface[]
     */
    private $serviceProviders;

    /**
     * ServicePool constructor.
     * @param ModuleConfigInterface $config
     * @param ServiceProviderInterface[] $serviceProviders
     */
    public function __construct(ModuleConfigInterface $config, $serviceProviders = [])
    {
        $this->config = $config;
        $this->serviceProviders = $serviceProviders;
    }

    /**
     * Obtain all available services, optionally configured with presets.
     *
     * @param mixed $store
     * @param string $destinationCountryId
     * @param ServiceSettingsInterface[] $servicePresets
     * @return ServiceCollection|ServiceInterface[]
     */
    public function getServices($store, $destinationCountryId, array $servicePresets = [])
    {
        $originCountryId = $this->config->getShipperCountry($store);
        $euCountries = $this->config->getEuCountryList($store);
        $services = [];

        foreach ($this->serviceProviders as $serviceProvider) {
            $providerServices = $serviceProvider->getServices(
                $originCountryId,
                $destinationCountryId,
                $euCountries,
                $servicePresets
            );
            $services = array_merge($services, $providerServices);
        }

        return ServiceCollection::fromArray($services);
    }
}
