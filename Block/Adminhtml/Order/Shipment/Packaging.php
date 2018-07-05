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

use \Dhl\Shipping\Model\Config\ModuleConfigInterface;
use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Json\EncoderInterface;
use \Magento\Shipping\Model\Carrier\Source\GenericInterface;
use \Magento\Framework\Registry;
use \Magento\Shipping\Model\CarrierFactory;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Packaging
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Packaging extends \Magento\Shipping\Block\Adminhtml\Order\Packaging
{
    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Packaging constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param GenericInterface $sourceSizeModel
     * @param Registry $coreRegistry
     * @param CarrierFactory $carrierFactory
     * @param ModuleConfigInterface $moduleConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        GenericInterface $sourceSizeModel,
        Registry $coreRegistry,
        CarrierFactory $carrierFactory,
        ModuleConfigInterface $moduleConfig,
        array $data = []
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $jsonEncoder, $sourceSizeModel, $coreRegistry, $carrierFactory, $data);
    }

    /**
     * @return bool
     */
    public function displayCustomsValue()
    {
        $destCountryId   = $this->getShipment()->getShippingAddress()->getCountryId();
        return $this->moduleConfig->isCrossBorderRoute($destCountryId, $this->getShipment()->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getStoreWeightUnit()
    {
        $weightUnit = strtoupper($this->scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getShipment()->getStoreId()
        ));

        return $weightUnit;
    }

    /**
     * @return bool
     */
    public function isMetricUnit()
    {
        $unit = $this->getStoreWeightUnit();
        return $unit != \Zend_Measure_Weight::LBS;
    }

    /**
     * @return mixed
     */
    public function getDefaultExportContentType()
    {
        $storeId = $this->getShipment()->getStoreId();
        return $this->moduleConfig->getDefaultExportContentType($storeId);
    }

    /**
     * @return mixed
     */
    public function getDefaultExportContentTypeExplanation()
    {
        $storeId = $this->getShipment()->getStoreId();
        return $this->moduleConfig->getDefaultExportContentTypeExplanation($storeId);
    }

    /**
     * @return array
     */
    public function getContainers()
    {
        $order = $this->getShipment()->getOrder();
        $storeId = $this->getShipment()->getStoreId();
        $address = $order->getShippingAddress();
        $carrier = $this->_carrierFactory->create($order->getShippingMethod(true)->getCarrierCode(), $storeId);
        $countryShipper = $this->_scopeConfig->getValue(
            \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($carrier) {
            $params = new \Magento\Framework\DataObject(
                [
                    'method' => $order->getShippingMethod(true)->getMethod(),
                    'country_shipper' => $countryShipper,
                    'country_recipient' => $address->getCountryId(),
                ]
            );
            return $carrier->getContainerTypes($params);
        }
        return [];
    }
}
