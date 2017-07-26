<?php

namespace Dhl\Shipping\Model\Shipping\Service;

interface ServiceInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return boolean
     */
    public function isEnabled();

    /**
     * @return boolean
     */
    public function isSelected();

    /**
     * @return bool
     */
    public function getValue();

    /**
     * @param string $value
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getApiClass();
}

