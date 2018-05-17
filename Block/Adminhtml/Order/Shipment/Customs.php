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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\Order\Shipment;

use Dhl\Shipping\Model\Adminhtml\System\Config\Source\ApiType;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\TermsOfTradeBcs;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\TermsOfTradeGla;
use Dhl\Shipping\Model\Attribute\Source\DGCategory;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Magento\Backend\Block\Template;

/**
 * Customs
 *
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Customs extends Template
{
    const BCS_CUSTOMS_TEMPLATE = 'Dhl_Shipping::order/packaging/popup_customs_bcs.phtml';

    const GL_CUSTOMS_TEMPLATE = 'Dhl_Shipping::order/packaging/popup_customs_gl.phtml';

    /*
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var DGCategory
     */
    private $dgCategoryAttribute;

    /**
     * @var TermsOfTradeBcs
     */
    private $bcsTerms;

    /**
     * @var TermsOfTradeGla
     */
    private $glaTerms;

    /**
     * Customs constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ModuleConfigInterface $moduleConfig
     * @param DGCategory $category
     * @param TermsOfTradeBcs $termsOfTradeBcs
     * @param TermsOfTradeGla $termsOfTradeGla
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        ModuleConfigInterface $moduleConfig,
        DGCategory $category,
        TermsOfTradeBcs $termsOfTradeBcs,
        TermsOfTradeGla $termsOfTradeGla,
        array $data = []
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->coreRegistry = $registry;
        $this->dgCategoryAttribute = $category;
        $this->bcsTerms = $termsOfTradeBcs;
        $this->glaTerms = $termsOfTradeGla;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->coreRegistry->registry('current_shipment');
    }

    /**
     * Get Currency Code for Custom Value
     *
     * @return string
     */
    public function getCustomValueCurrencyCode()
    {
        $orderInfo = $this->getShipment()->getOrder();

        return $orderInfo->getBaseCurrency()->getCurrencyCode();
    }

    /**
     * Return Template based on origin country and crossborder shipping.
     *
     * @return string
     */
    public function getTemplate()
    {
        $destCountryId   = $this->getShipment()->getShippingAddress()->getCountryId();
        $isCrossBorder = $this->moduleConfig->isCrossBorderRoute($destCountryId, $this->getShipment()->getStoreId());
        $apiType = $this->moduleConfig->getApiType($this->getShipment()->getStoreId());
        $usedTemplate = '';
        if ($isCrossBorder && $apiType === ApiType::API_TYPE_BCS) {
            $usedTemplate = self::BCS_CUSTOMS_TEMPLATE;
        } elseif ($isCrossBorder && $apiType === ApiType::API_TYPE_GLA) {
            $usedTemplate = self::GL_CUSTOMS_TEMPLATE;
        }

        return $usedTemplate;
    }

    /**
     * @return array
     */
    public function getDangerousGoodsCategoryOptions()
    {
        return $this->dgCategoryAttribute->toOptionArray();
    }

    /**
     * Get Options for Terms of Trade
     *
     * @return mixed[]
     */
    public function getTermsOfTradeOptions()
    {
        $api = $this->moduleConfig->getApiType($this->getShipment()->getStoreId());

        if ($api === ApiType::API_TYPE_BCS) {
            return $this->bcsTerms->toOptionArray();
        }

        return $this->glaTerms->toOptionArray();
    }

    /**
     * Get Terms of Trade default value for current scope
     *
     * @return mixed
     */
    public function getTermsOfTradeDefault()
    {
        $scopeId = $this->getShipment()->getStoreId();
        $terms = $this->moduleConfig->getTermsOfTrade($scopeId);

        return $terms;
    }

    /**
     * Get Additional Fee default value for current scope
     * @return mixed
     */
    public function getAdditionalFeeDefault()
    {
        $scopeId = $this->getShipment()->getStoreId();
        return $this->moduleConfig->getDefaultAdditionalFee($scopeId);
    }

    /**
     * Get Place of Commital default value for current scope
     *
     * @return string
     */
    public function getPlaceOfCommitalDefault()
    {
        $scopeId = $this->getShipment()->getStoreId();
        return $this->moduleConfig->getDefaultPlaceOfCommital($scopeId);
    }
}
