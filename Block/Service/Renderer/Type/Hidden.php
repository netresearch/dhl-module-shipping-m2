<?php
/**
 * Dhl Versenden
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
 * PHP version 5
 *
 * @category  Dhl
 * @package   Dhl\Versenden\Bcs\Api\Service
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Block\Service\Renderer\Type;

/**
 * Hidden
 *
 * @category Dhl
 * @package  Dhl\Versenden\Bcs\Api\Service
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
abstract class Hidden extends Generic
{
    private $frontendInputType = 'hidden';

    /**
     * @return string
     */
    public function getSelectorHtml()
    {
        $format = '<input type="hidden" name="shipment_service[%s]" value="%s">';
        return sprintf($format, $this->getCode(), $this->isSelected());
    }

    /**
     * No labels for hidden form elements.
     *
     * @return string
     */
    public function getLabelHtml()
    {
        return '';
    }

    /**
     * No service details for hidden form elements.
     *
     * @return string
     */
    public function getValueHtml()
    {
        return '';
    }
}
