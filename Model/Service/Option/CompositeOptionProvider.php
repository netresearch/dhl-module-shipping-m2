<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 16.10.18
 * Time: 16:28
 */

namespace Dhl\Shipping\Model\Service\Option;


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
     */
    public function enhanceServicesWithOptions($services, $args)
    {
        foreach ($this->providers as $provider) {
            if(array_key_exists($provider::SERVICE_CODE, $services)) {
                $provider->enhanceServiceWithOptions($services[$provider::SERVICE_CODE], $args);
            }
        }
    }
}