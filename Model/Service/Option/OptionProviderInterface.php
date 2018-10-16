<?php

namespace Dhl\Shipping\Model\Service\Option;

interface OptionProviderInterface
{
    const SERVICE_CODE = '';

    /**
     * @param array $service
     * @param array $args
     */
    public function enhanceServiceWithOptions($service, $args);

}