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
 * @author    Rico Sonntag <rico.sonntag@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Traits;

use ReflectionClass;
use ReflectionException;

/**
 * Class EscapeHtmlAttrTrait
 *
 * @category Dhl
 * @author   Rico Sonntag <rico.sonntag@netresearch.de>
 */
trait EscapeHtmlAttrTrait
{
    /**
     * The method checks if a Magento parent class contains a specific method.
     *
     * @param string $method The method name
     *
     * @return bool TRUE if method exists, FALSE otherwise
     */
    private function parentMethodExists($method)
    {
        try {
            $reflectionClass = new ReflectionClass($this);

            while ($reflectionClass) {
                if ($reflectionClass->hasMethod($method)
                    && (strpos($reflectionClass->getNamespaceName(), 'Magento') === 0)
                ) {
                    return true;
                }

                $reflectionClass = $reflectionClass->getParentClass();
            }
        } catch (ReflectionException $ex) {
            // Ignore
        }

        return false;
    }

    /**
     * Escape a string for the HTML attribute context.
     *
     * @param string  $string
     * @param boolean $escapeSingleQuote
     *
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        // Available only in Magento 2.2+
        if ($this->parentMethodExists('escapeHtmlAttr')) {
            return parent::escapeHtmlAttr($string, $escapeSingleQuote);
        }

        return $this->escapeHtml($string);
    }
}
