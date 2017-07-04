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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\Order\Shipment;
use Dhl\Shipping\Api\Config\ModuleConfigInterface;
use Dhl\Shipping\Api\Util\ShippingRoutesInterface;

/**
 * Customs
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Customs extends \Magento\Backend\Block\Template
{
    const BCS_CUSTOMS_TEMPLATE = 'Dhl_Shipping::order/packaging/popup_customs_bcs.phtml';

    const GL_CUSTOMS_TEMPLATE = 'Dhl_Shipping::order/packaging/popup_customs_gl.phtml';

    /** @var  ShippingRoutesInterface */
    private $shippingRoutes;

    /** @var  ModuleConfigInterface */
    private $moduleConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        ModuleConfigInterface $moduleConfig,
        ShippingRoutesInterface $shippingRoutes,
        array $data = []
    ) {
        $this->shippingRoutes = $shippingRoutes;
        $this->moduleConfig = $moduleConfig;
        $this->coreRegistry = $registry;
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
     * @return mixed[]
     */
    public function getTermsOfTrade()
    {

        $terms = [
            [
                'value' => '',
                'label' => __('--Please Select--')
            ]
        ];

        return $terms;
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
     * Return Template bases on origin country and crossborder shipping.
     *
     * @return string
     */
    public function getTemplate()
    {

        $originCountryId = $this->moduleConfig->getOriginCountry($this->getShipment()->getStoreId());
        $destCountryId   = $this->getShipment()->getShippingAddress()->getCountryId();
        $euCountries     = $this->moduleConfig->getEuCountryList($this->getShipment()->getStoreId());
        $bcsCountries    = ['DE','AT'];

        $isCrossBorder = $this->shippingRoutes->isCrossBorderRoute($originCountryId, $destCountryId, $euCountries);
        $usedTemplate  = '';

        return self::GL_CUSTOMS_TEMPLATE;

        if ($isCrossBorder && in_array($originCountryId, $bcsCountries)) {
            $usedTemplate = self::BCS_CUSTOMS_TEMPLATE;
        } elseif ($isCrossBorder && !in_array($originCountryId, $bcsCountries)) {
            $usedTemplate = self::GL_CUSTOMS_TEMPLATE;
        }

        return $usedTemplate;
    }
}
