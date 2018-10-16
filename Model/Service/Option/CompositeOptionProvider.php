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

namespace Dhl\Shipping\Model\Service\Option;

/**
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CompositeOptionProvider
{

    /**
     * @var OptionProviderInterface[]
     */
    private $providers = [];

    /**
     * CompositeOptionProvider constructor.
     * @param OptionProviderInterface[] $providers
     */
    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    /**
     * @param array $services
     * @param array $args
     * @return array
     */
    public function enhanceServicesWithOptions($services, $args)
    {
        foreach ($this->providers as $provider) {
            $serviceCode = $provider->getServiceCode();
            if(array_key_exists($serviceCode, $services)) {
                $service = $provider->enhanceServiceWithOptions($services[$serviceCode], $args);
                $services[$serviceCode] = $service;
            }
        }

        return $services;
    }
}