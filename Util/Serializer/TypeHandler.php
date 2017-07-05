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
 * @category  Dhl
 * @package   Dhl\Shipping
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Util\Serializer;

use \Magento\Framework\ObjectManagerInterface;
use \Dhl\Shipping\Util\Serializer\Reflection\ReflectionInterface;
use \Dhl\Shipping\Util\Serializer\Reflection\AbstractTypeHandler;

/**
 * API Type Instantiator Utility using M2 object manager
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class TypeHandler extends AbstractTypeHandler
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager = null;

    /**
     * TypeHandler constructor.
     * @param ReflectionInterface $reflect
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ReflectionInterface $reflect, ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
        parent::__construct($reflect);
    }

    /**
     * Generic API type factory method.
     *
     * @param string $type
     * @return mixed
     */
    public function create($type)
    {
        $type = preg_replace('/\[\]$/', '', $type);
        $type = preg_replace('/Interface$/', '', $type);
        return $this->objectManager->create($type);
    }
}
