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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Util;

use Magento\Framework\Escaper as CoreEscaper;

/**
 * Escaper
 *
 * @package  Dhl\Shipping\Util
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Escaper
{
    /**
     * @var CoreEscaper
     */
    private $coreEscaper;

    /**
     * Escaper constructor.
     * @param CoreEscaper $coreEscaper
     */
    public function __construct(CoreEscaper $coreEscaper)
    {
        $this->coreEscaper = $coreEscaper;
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
        if ($escapeSingleQuote) {
            return $this->coreEscaper->escapeHtmlAttr((string) $string);
        }

        return htmlspecialchars($string, ENT_COMPAT, 'UTF-8', false);
    }
}
