<?php

namespace Dhl\Shipping\Model\Shipping\Service;

class ServiceFactory
{

    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Dhl\Shipping\Model\Shipping\Service\ServiceInterface
     */
    public function create(string $serviceCode, array $data = [])
    {
        $libServiceClass = \Dhl\Shipping\Service\ServiceFactory::get($serviceCode);
        $data['apiService'] = $libServiceClass;

        return $this->objectManager->create(Service::class, $data);
    }
}
