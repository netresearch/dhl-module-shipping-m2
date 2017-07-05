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

use \Magento\Framework\Reflection\TypeProcessor;
use \Dhl\Shipping\Util\Serializer\Reflection\ReflectionInterface;

/**
 * API Type Reflection Utility using ZF2
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Reflection implements ReflectionInterface
{
    /**
     * @var TypeProcessor
     */
    private $typeProcessor;

    /**
     * Reflection constructor.
     * @param TypeProcessor $typeProcessor
     */
    public function __construct(TypeProcessor $typeProcessor)
    {
        $this->typeProcessor = $typeProcessor;
    }

    /**
     * @param \stdClass $type
     * @param string $property
     * @return string
     */
    public function getPropertyType($type, $property)
    {
        try {
            $reflectionClass = new \Zend\Code\Reflection\ClassReflection($type);
            /** @var \Zend\Code\Reflection\DocBlock\Tag\GenericTag $tag */
            $tag = $reflectionClass->getProperty($property)->getDocBlock()->getTag('var');
        } catch (\ReflectionException $e) {
            return null;
        }

        return $tag->getContent();
    }

    /**
     * @param \stdClass $type
     * @param string $getter
     * @return mixed
     */
    public function getReturnValueType($type, $getter)
    {
        try {
            $reflectionMethod = new \Zend\Code\Reflection\MethodReflection($type, $getter);
            $typeInfo = $this->typeProcessor->getGetterReturnType($reflectionMethod);
        } catch (\ReflectionException $e) {
            return null;
        }

        return $typeInfo['type'];
    }
}
