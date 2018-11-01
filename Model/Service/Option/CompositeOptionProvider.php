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

use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CompositeOptionProvider constructor.
     *
     * @param OptionProviderInterface[] $providers
     * @param LoggerInterface $logger
     */
    public function __construct(
        array $providers,
        LoggerInterface $logger
    ) {
        $this->providers = $providers;
        $this->logger = $logger;
    }

    /**
     * @param string[][] $services
     * @param string[] $args
     * @return string[][]
     */
    public function enhanceServicesWithOptions($services, $args)
    {
        foreach ($this->providers as $provider) {
            $serviceCode = $provider->getServiceCode();
            if (array_key_exists($serviceCode, $services)) {
                try {
                    $service = $provider->enhanceServiceWithOptions($services[$serviceCode], $args);
                    $services[$serviceCode] = $service;
                } catch (\Exception $e) {
                    // something went wrong, remove service from output array
                    unset($services[$serviceCode]);
                    $this->logger->warning(
                        "Service with code {$serviceCode} was removed due to error. "
                        . 'Message: ' . $e->getMessage()
                    );
                }
            }
        }

        return $services;
    }
}
