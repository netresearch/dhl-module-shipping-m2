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
 * @package   Dhl\Shipping\Model
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Adminhtml\System\Config\Backend;

use Dhl\Shipping\Util\ShippingProducts\ShippingProductsInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * DefaultProduct
 *
 * Backend model MUST NOT extend ArraySerialized / implement ProcessorInterface.
 * Since M2.2.1, ArraySerialized::afterLoad will not be called.
 *
 * @see \Magento\Config\Block\System\Config\Form::getFieldData
 * @see \Dhl\Shipping\Block\Adminhtml\System\Config\Form\Field\DefaultProduct::render
 *
 * @package Dhl\Shipping\Model
 * @author  Andreas Müller <andreas.mueller@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.netresearch.de/
 */
class DefaultProduct extends Value
{
    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * DefaultProduct constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ShippingProductsInterface $shippingProducts
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ShippingProductsInterface $shippingProducts,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->shippingProducts = $shippingProducts;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * If only one single product is applicable to the configured shipping origin,
     * then it is displayed as text field in UI. Normalize to product code, e.g.:
     *
     * - in: ['MY' => 'DHL Parcel Domestic']
     * - out: ['MY' => 'PDO']
     *
     * @param string[] $productsConfig
     * @return string[]
     */
    private function convertProductNameToProductCode(array $productsConfig)
    {
        $scopeId = $this->getScopeId();
        $shippingOrigin = $this->_config->getValue(
            ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
            ScopeInterface::SCOPE_WEBSITE,
            $scopeId
        );
        $routeOptions = $this->shippingProducts->getAvailableShippingRoutes($shippingOrigin);

        foreach ($productsConfig as $originId => &$selectedProduct) {
            if (count($routeOptions[$originId]) === 1) {
                $selectedProduct = $routeOptions[$originId][0];
            }
        }

        return $productsConfig;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $shippingConfig = $this->getData('groups/dhlshipping/fields');
        $productConfig = $shippingConfig[$this->getData('field')] ?? [];
        $value = $this->convertProductNameToProductCode($productConfig);

        if (!empty($value)) {
            $value = json_encode($value);
        } else {
            $value = $this->getOldValue();
        }

        $this->setValue($value);

        parent::beforeSave();

        return $this;
    }

    /**
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = empty($value) ? [] : $this->processValue($value);
        $this->setValue($value);
    }

    /**
     * @param string $value
     * @return string[]
     */
    public function processValue($value)
    {
        $result = json_decode($value, true);
        return $result;
    }

    /**
     * @return string
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
