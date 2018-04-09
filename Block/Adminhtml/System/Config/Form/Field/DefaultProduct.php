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
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Dhl\Shipping\Util\ShippingProductsInterface;
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

    protected $isCollapsedDefault = true;

    /**
     * Field renderer
     *
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_fieldRenderer;

    const DEFAULT_TEMPLATE= 'Dhl_Shipping::system/config/defaults.phtml';

    public function __construct(Context $context, ShippingProductsInterface $shippingProducts, array $data = [])
    {
        $this->shippingProducts = $shippingProducts;
        parent::__construct($context, $data);
    }

    /**
     * @param Fieldset $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '';

        $configValue = $element->getValue();

        $routes = $this->getAvailableOptions();
        foreach ($routes as $key => $value) {
            $options = $this->getProductNames($value);
            $count = count($options);

            $type = $count > 1 ? 'select' : 'text';
            if ($count > 1) {
                $type = 'select';
                $config = [
                    'name' => 'groups[dhlshipping][fields][default_shipping_product]['.$key.']',
                    'label' => __('Ship to ').$key,
                    'title' => __('Ship to ').$key,
                    'values' => $options,
                    'value' => isset($configValue[$key]) ? $configValue[$key] : '',
                ];
            } else {
                $type = 'text';
                $config = [
                    'name' => 'groups[dhlshipping][fields][default_shipping_product]['.$key.']',
                    'label' => __('Ship to ').$key,
                    'title' => __('Ship to ').$key,
                    'value' => isset($configValue[$key]) ? $configValue[$key] : $options[0]['value'],
                ];
            }

            $element->addField('default_product_'.$key, $type, $config)->setRenderer(
                $this->getLayout()->getBlockSingleton(
                    \Magento\Config\Block\System\Config\Form\Field::class
                )
            );
            if ($type == 'text') {
                $element->setReadonly(true, true);
            }

        }
        return parent::render($element);
    }

    /**
     * Get dummy element
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new \Magento\Framework\DataObject(['showInDefault' => 1, 'showInWebsite' => 1]);
        }
        return $this->_dummyElement;
    }

    /**
     * Get field renderer
     *
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->getLayout()->getBlockSingleton(
                \Magento\Config\Block\System\Config\Form\Field::class
            );
        }
        return $this->_fieldRenderer;
    }

    /**
     * @param string[] $items
     * @return string[]
     */
    private function filterAvailable($items)
    {
        $scopeId = $this->_request->getParam('website', 0);
        $shippingOrigin = $this->_scopeConfig->getValue(
            ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeId
        );
        $applicableCodes = $this->shippingProducts->getApplicableCodes($shippingOrigin);

        $items = array_filter($items, function ($item) use ($applicableCodes) {
            return in_array($item['value'], $applicableCodes);
        });

        return $items;
    }

    private function getAvailableOptions()
    {
        $scopeId = $this->_request->getParam('website', 0);
        $shippingOrigin = $this->_scopeConfig->getValue(
            ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeId
        );
        $routes = $this->shippingProducts->getAvailableShippingRoutes($shippingOrigin);
        $applicableCodes = $this->shippingProducts->getApplicableCodes($shippingOrigin);
        return $routes;
    }

    public function getProductNames($items)
    {
        $values = [];
        foreach ($items as $item => $value) {
            array_push($values, ['value' => $value, 'label' => $this->shippingProducts->getProductName($value), 'selected' => 'selected']);
        }
        return $values;
    }
}
