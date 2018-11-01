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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Config;

use Dhl\Shipping\Service;

/**
 * ServiceConfig
 *
 * @deprecated
 * @see \Dhl\Shipping\Model\Service\ServicePool
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceConfig implements ServiceConfigInterface
{
    /**
     * @var ConfigAccessorInterface
     */
    private $configAccessor;

    /**
     * BcsService constructor.
     *
     * @param ConfigAccessorInterface $configAccessor
     * @param Service\ServiceFactory $serviceFactory
     */
    public function __construct(
        ConfigAccessorInterface $configAccessor,
        Service\ServiceFactory $serviceFactory
    ) {
        $this->configAccessor = $configAccessor;
    }

    /**
     * Load all DHL additional service models.
     *
     * @param mixed $store
     *
     * @return Service\ServiceCollection
     */
    public function getServices($store = null)
    {
        $services = [];
        $serviceCodes = [
            // customer/checkout services
            Service\ParcelAnnouncement::CODE,
            Service\PreferredDay::CODE,
            Service\PreferredTime::CODE,
            Service\PreferredLocation::CODE,
            Service\PreferredNeighbour::CODE,
            // merchant/admin services
            Service\BulkyGoods::CODE,
            Service\Insurance::CODE,
            Service\PrintOnlyIfCodeable::CODE,
            Service\ReturnShipment::CODE,
            Service\VisualCheckOfAge::CODE,
        ];

        foreach ($serviceCodes as $serviceCode) {
            // read config
            $path = strtolower("carriers/dhlshipping/shipment_service_{$serviceCode}");
            $serviceValue = $this->configAccessor->getConfigValue($path, $store);

            $service = Service\ServiceFactory::get($serviceCode, $serviceValue);
            $services[$serviceCode] = $service;
        }

        return Service\ServiceCollection::fromArray($services);
    }
}
