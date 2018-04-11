<?php
/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 09.04.18
 * Time: 12:30
 */

namespace Dhl\Shipping\Model\Adminhtml\System\Config\Serialized;

use Magento\Shipping\Model\Config as ShippingConfig;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Dhl\Shipping\Util\ShippingProductsInterface;
use Magento\Store\Model\ScopeInterface;

class DefaultProduct extends Value
{
    private $shippingProducts;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        ShippingProductsInterface $shippingProducts,
        ShippingConfig $shippingConfig,
        array $data = []
    ) {
        $this->shippingProducts = $shippingProducts;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $groups = $this->getData('groups');
        $value = $groups['dhlshipping']['fields'][$this->getData('field')];
        $scopeId = $this->getScopeId();
        $shippingOrigin = $this->_config->getValue(
            ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeId
        );


        $routeOptions = $this->shippingProducts->getAvailableShippingRoutes($shippingOrigin);
        foreach ($routeOptions as $option => $optionValue) {
            if (count($optionValue) == 1) {
                $value[$option] = $optionValue[0];
            }
        }
        if (is_array($value)) {
            unset($value['__empty']);
        }

        if (!empty($value)) {
            $value = json_encode($value);
        } else {
            $value = $this->getOldValue();
        }

        $this->setValue($value);

        parent::beforeSave();

        return $this;
    }

    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            $this->setValue(empty($value) ? false : $this->processValue($value));
        }
    }

    public function processValue($value)
    {
        $result = json_decode($value, true);
        return $result;
    }

    public function getOldValue()
    {
        $oldValue = $this->_config->getValue(
            $this->getPath(),
            $this->getScope() ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->getScopeCode()
        );

        if (is_array($oldValue)) {
            $oldValue = json_encode($oldValue);
        }

        return $oldValue;
    }
}
