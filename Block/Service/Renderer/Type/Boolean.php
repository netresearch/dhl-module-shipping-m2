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
 * Boolean
 *
 * @category Dhl
 * @package  Dhl\Versenden\Bcs\Api\Service
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
abstract class Boolean extends Generic
{
    protected $frontendInputType = 'boolean';

    /**
     * @return string
     */
    public function getSelectorHtml()
    {
        $format = <<<'HTML'
<input type="checkbox" id="shipment_service_%s" name="shipment_service[%s]" value="%s" class="checkbox" %s />
HTML;

        $selected = (bool)$this->isSelected() ? 'checked="checked"' : '';
        return sprintf($format, $this->getCode(), $this->getCode(), $this->getCode(), $selected);
    }

    /**
     * @return string
     */
    public function getLabelHtml()
    {
        $format = <<<'HTML'
<label for="shipment_service_%s">%s</label>
HTML;

        return sprintf($format, $this->getCode(), $this->getName());
    }

    /**
     * No service details for boolean form elements.
     *
     * @return string
     */
    public function getValueHtml()
    {
        return '';
    }
}
