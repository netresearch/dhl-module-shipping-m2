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

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $comment = $element->getData('comment');
        if ($comment) {
            $comment = "<p class='note'>$comment</p>";
        }

        $html = '<td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label></td>';
        $html .=
            sprintf('<td class="value">'.
                    '<fieldset class="dhlshipping_default_product">%s</fieldset>'.
                    ' %s</td>', $this->renderChildFields($element), $comment);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * @param AbstractElement $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml(AbstractElement $element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    private function renderChildFields(AbstractElement $element)
    {
        $configValue = $element->getValue();

        $routes = $this->getAvailableOptions();
        $html = '';
        foreach ($routes as $key => $value) {
            $element->removeField('default_product_' . $key);
            $options = $this->getProductNames($value);

            $count = count($options);
            if ($count > 1) {
                $config = [
                    'name' => 'groups[dhlshipping][fields][default_shipping_products][' . $key . ']',
                    'label' => __('Ship to ') . $key,
                    'title' => __('Ship to ') . $key,
                    'values' => $options,
                    'value' => isset($configValue[$key]) ? $configValue[$key] : ''
                ];
                $html .= $this->renderSelect('default_product_' . $key, $config);
            } elseif ($count === 1) {
                $config = [
                    'name' => 'groups[dhlshipping][fields][default_shipping_products][' . $key . ']',
                    'label' => __('Ship to ') . $key,
                    'title' => __('Ship to ') . $key,
                    'readonly' => true,
                    'value' => isset($configValue[$key]) ?
                        $this->shippingProducts->getProductName($configValue[$key]) : $options[0]['label']
                ];
                $html .= $this->renderTextInput('default_product_' . $key, $config);
            }
        }

        return $html;
    }

    /**
     * @param string $elemId
     * @param string[] $config
     * @return string
     */
    private function renderTextInput($elemId, array $config)
    {
        $readonly = $config['readonly'] ? 'readonly' : '';
        $html = '<div class="admin__field field field-'.$elemId.'">';
        $html .= '<label for="'.$elemId.'" class="label admin__field-label">'.$config['label'].'</label>';
        $html .= '<div class="admin__field-control control"><input type="text" '.
            'name="'.$config['name'].'" '.
            'value="'.$config['value'].'" '.
            'class="input-text admin__control-text"'.
            'title="'.$config['title'].'"'.
            $readonly. '></div>';
        $html .= '</div>';
        return $html;
    }

    /**
     * @param $elemId
     * @param string[] $config
     * @return string
     */
    private function renderSelect($elemId, array $config)
    {
        $fieldValue = !empty($config['value']) ? $config['value'] : false;
        $html = $html = '<div class="admin__field field field-'.$elemId.'">';
        $html .= '<label for="'.$elemId.'" class="label admin__field-label">'.$config['label'].'</label>';
        $html .= '<div class="admin__field-control control">';
        $html .= '<select id="'.$elemId.'" name="'.$config['name'].'" title="'.$config['title'].'">'.
            $this->getOptionsHtml($config['values'], $fieldValue) . '</select>';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * @param string[] $options
     * @param bool $fieldValue
     * @return string
     */
    private function getOptionsHtml(array $options, $fieldValue = false)
    {
        $html ='';
        foreach ($options as $option) {
            $selected = (isset($fieldValue) && $option['value'] === $fieldValue) ? 'selected="selected"' : '';
            $html .= '<option value="'.$option['value'].'" '.$selected.'>'.$option['label'].'</option>';
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
            $values[] =
                [
                    'value' => $value,
                    'label' => $this->shippingProducts->getProductName($value)
                ];
        }
        return $values;
    }
}
