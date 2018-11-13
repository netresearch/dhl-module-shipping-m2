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
 * @package   Dhl\Shipping\Util
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Util;

use InvalidArgumentException;

/**
 * API Type Reflection Utility using ZF2
 *
 * @author  Rico Sonntag <rico.sonntag@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.netresearch.de/
 * @see     https://github.com/magento/magento2/blob/2.2.0/lib/internal/Magento/Framework/Serialize/Serializer/Json.php
 */
class Serializer
{
    /**
     * JSON encodes a string.
     *
     * @param array $data
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function serialize($data)
    {
        $result = json_encode($data);

        if (false === $result) {
            throw new InvalidArgumentException('Unable to serialize value.');
        }

        return $result;
    }

    /**
     * JSON decodes a string.
     *
     * @param string $string
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function unserialize($string)
    {
        $result = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Unable to unserialize value.');
        }

        return $result;
    }
}
