<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dhl\Versenden\Model\ResourceModel;

use \Dhl\Versenden\Api\Data\VersendenInfoOrderInterface;
use \Dhl\Versenden\Api\Data\VersendenInfoQuoteInterface;
use \Magento\Framework\ObjectManagerInterface;

/**
 * Class Metadata
 */
class Metadata
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $resourceClassName;

    /**
     * @var string
     */
    private $modelClassName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string                 $resourceClassName
     * @param string                 $modelClassName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $resourceClassName,
        $modelClassName
    ) {
        $this->objectManager     = $objectManager;
        $this->resourceClassName = $resourceClassName;
        $this->modelClassName    = $modelClassName;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getMapper()
    {
        return $this->objectManager->get($this->resourceClassName);
    }

    /**
     * @return VersendenInfoOrderInterface | VersendenInfoQuoteInterface
     */
    public function getNewInstance()
    {
        return $this->objectManager->create($this->modelClassName);
    }
}
