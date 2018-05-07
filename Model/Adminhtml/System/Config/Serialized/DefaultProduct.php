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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Adminhtml\System\Config\Serialized;

use Dhl\Shipping\Util\ShippingProductsInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * DefaultProduct
 *
 * @package  Dhl\Shipping
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class DefaultProduct extends Value
{
    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * DefaultProduct constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param ShippingProductsInterface $shippingProducts
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        ShippingProductsInterface $shippingProducts,
        array $data = []
    ) {
        $this->shippingProducts = $shippingProducts;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
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
        $value = empty($value) ? [] : $this->processValue($value);
        $this->setValue($value);
    }

    /**
     * @param $value
     * @return array
     */
    public function processValue($value)
    {
        $result = json_decode($value, true);
        return $result;
    }

    /**
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
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
