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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Service;

use Dhl\Shipping\Api\Data\ServiceInterface;

/**
 * ServiceCollection
 *
 * @package  Dhl\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceCollection extends \ArrayIterator
{
    /**
     * @param ServiceInterface[] $services
     * @return static
     */
    public static function fromArray($services)
    {
        $codes = array_map(function (ServiceInterface $service) {
            return $service->getCode();
        }, $services);

        $services = array_combine($codes, $services);
        return new static($services);
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback)
    {
        $filteredServices = array_filter($this->getArrayCopy(), $callback);
        return static::fromArray($filteredServices);
    }

    /**
     * @param callable $callback
     * @return mixed[]
     */
    public function map(callable $callback)
    {
        $mappedServices = array_map($callback, $this->getArrayCopy());
        return $mappedServices;
    }
}
