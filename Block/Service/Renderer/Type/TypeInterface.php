<?php

namespace Dhl\Shipping\Block\Service\Renderer\Type;

/**
 * Class TypeInterface
 *
 * @package \Dhl\Shipping\Block\Service\Service\Type
 */
interface TypeInterface
{
    public function getFrontendInputType();

    public function getSelectorHtml();

    /**
     * @return string
     */
    public function getLabelHtml();

    /**
     * No service details for boolean form elements.
     *
     * @return string
     */
    public function getValueHtml();
}
