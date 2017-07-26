<?php

namespace Dhl\Shipping\Block\Service\Renderer;

use Dhl\Shipping\Block\Service\Service\Type\TypeInterface;
use Dhl\Shipping\Model\Shipping\Service\ServiceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\Phrase;

/**
 * Class ServiceRenderer
 *
 * @package \\${NAMESPACE}
 */
class ServiceRenderer
{
    /**
     * @var array
     */
    private $rendererMappings;

    /**
     * ServiceRenderer constructor.
     * @param array $mappings
     *
     */
    public function __construct(ObjectManager $objectManager, array $mappings = [])
    {
        /**
         * In the mappings is defined which class -> to which renderer type
         */
        $this->rendererMappings = $mappings;
        $this->objectManager = $objectManager;
    }

    /**
     * @param ServiceInterface $service
     * @return TypeInterface
     * @throws LocalizedException
     */
    private function getServiceRenderer(ServiceInterface $service)
    {
        $class = $service->getApiClass();
        if (!isset($this->rendererMappings[$class])) {
            throw  new LocalizedException(__('No renderer for this Service class $1', $class));
        }
        $renderer = $this->objectManager->create($this->rendererMappings[$class], ['service' => $service]);

        if (!$renderer instanceof TypeInterface) {
            throw  new LocalizedException(__('Wrong renderer for this Service class $1', $class));
        }
        return $renderer;
    }

    public function getLabelHtml(ServiceInterface $service)
    {
        $this->getServiceRenderer($service)->getLabelHtml();
    }

    public function getValueHtml(ServiceInterface $service)
    {
        $this->getServiceRenderer($service)->getValueHtml();
    }

    public function getSelectorHtml(ServiceInterface $service)
    {
        $this->getServiceRenderer($service)->getSelectorHtml();
    }

    public function getFrontendInputType(ServiceInterface $service)
    {
        $this->getServiceRenderer($service)->getFrontendInputType();
    }
}
