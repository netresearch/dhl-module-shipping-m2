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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Dhl\Shipping\Model\Adminhtml\System\Config\Source\TermsOfTrade as Source;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Dhl\Shipping\Model\Config\ModuleConfigInterface as ShippingConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * Config field block for the Terms of Trade.
 * The field is used as reference for default terms of trade for automatic shipment creation.
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Andreas Müller <andreas.muellerr@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class TermsOfTrade extends Field
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->getData('value')) {
            return parent::_getElementHtml($element);
        }

        $scopeId = $this->_request->getParam('website', 0);
        $apiType = $this->_scopeConfig->getValue(
            ShippingConfig::CONFIG_XML_PATH_API_TYPE,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeId
        );

        if ($apiType == 'bcs') {
            $element->setData(
                'values',
                [
                    ['value' => Source::TOD_DDP, 'label' => Source::TOD_DDP],
                    ['value' => Source::TOD_DDX, 'label' => Source::TOD_DDX],
                    ['value' => Source::TOD_DXV, 'label' => Source::TOD_DXV],
                    ['value' => Source::TOD_DDU, 'label' => Source::TOD_DDU]
                ]
            );
        } else {
            $element->setData(
                'values',
                [
                    ['value' => Source::TOD_DDP, 'label' => Source::TOD_DDP],
                    ['value' => Source::TOD_DDU, 'label' => Source::TOD_DDU]
                ]
            );
        }

        return parent::_getElementHtml($element);
    }

}
