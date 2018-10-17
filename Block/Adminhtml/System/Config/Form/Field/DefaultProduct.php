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
 * @package   Dhl\Shipping
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Dhl\Shipping\Util\ShippingProducts\ShippingProductsInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * Config field block for the Default Product select field.
 * Filters options based on the configured shipping origin.
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class DefaultProduct extends Field
{
    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * DefaultProduct constructor.
     * @param Context $context
     * @param ShippingProductsInterface $shippingProducts
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShippingProductsInterface $shippingProducts,
        array $data = []
    ) {
        $this->shippingProducts = $shippingProducts;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $comment = $element->getData('comment');
        if ($comment) {
            $comment = "<p class='note'>$comment</p>";
        }
        $htmlId = $element->getHtmlId();

        $html = '<td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label></td>';
        $html .=
            sprintf('<td class="value">'.
                    '<fieldset class="dhlshipping_default_product">%s</fieldset>'.
                    ' %s</td>', $this->renderChildren($element), $comment);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Decorate field row html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @param string                                               $html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _decorateRowHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }

    private function renderChildren(AbstractElement $element)
    {
        $configValue = $element->getValue();

        $routes = $this->getAvailableOptions();
        $html = '';
        foreach ($routes as $key => $value) {
            $element->removeField('default_product_' . $key);
            $options = $this->getProductNames($value);

            $count = count($options);
            if ($count > 1) {
                $type = 'select';
                $config = [
                    'name' => 'groups[dhlshipping][fields][default_shipping_products][' . $key . ']',
                    'label' => __('Ship to ') . $key,
                    'title' => __('Ship to ') . $key,
                    'values' => $options,
                    'value' => isset($configValue[$key]) ? $configValue[$key] : ''
                ];
                $html .= $this->renderSelect('default_product_' . $key, $config);
                #$element->addField('default_product_' . $key, $type, $config);
            } elseif ($count === 1) {
                $type = 'text';
                $config = [
                    'name' => 'groups[dhlshipping][fields][default_shipping_products][' . $key . ']',
                    'label' => __('Ship to ') . $key,
                    'title' => __('Ship to ') . $key,
                    'readonly' => true,
                    'value' => isset($configValue[$key]) ?
                        $this->shippingProducts->getProductName($configValue[$key]) : $options[0]['label']
                ];
                $html .= $this->renderTextInput('default_product_' . $key, $config);
                #$element->addField('default_product_' . $key, $type, $config);
            }
        }
        #$element->addClass('dhlshipping_default_product');

        return $html;
    }

    private function renderTextInput($elemId, $config)
    {
        $readonly = $config['readonly'] ? 'readonly' : '';
        $html = '<div>';
        $html .= '<label for="'.$elemId.'">'.$config['label'].'</label>';
        $html .= '<input type="text" '.
            'name="'.$config['name'].'" '.
            'value="'.$config['value'].'" '.
            $readonly. '>';
        $html .= '</div>';
        return $html;
    }
    private function renderSelect($elemId, $config)
    {
        $html = $html = '<div>';
        $html .= '<label for="'.$elemId.'">'.$config['label'].'</label>';
        $html .= '<select id="'.$elemId.'">'.$this->getOptions($config['values']) . '</select>';
        $html .= '</div>';

        return $html;
    }

    private function getOptions($options)
    {
        $html ='';
        foreach ($options as $option) {
            $html .= '<option value="'.$option['value'].'">'.$option['label'].'</option>';
        }
        return $html;
    }
    /**
     * @return mixed
     */
    private function getAvailableOptions()
    {
        $scopeId = $this->_request->getParam('website', 0);
        $shippingOrigin = $this->_scopeConfig->getValue(
            ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeId
        );
        $routes = $this->shippingProducts->getAvailableShippingRoutes($shippingOrigin);

        return $routes;
    }

    /**
     * @param $items
     * @return array
     */
    public function getProductNames($items)
    {
        $values = [];
        foreach ($items as $item => $value) {
            array_push(
                $values,
                [
                    'value' => $value,
                    'label' => $this->shippingProducts->getProductName($value),
                    'selected' => 'selected'
                ]
            );
        }
        return $values;
    }
}
