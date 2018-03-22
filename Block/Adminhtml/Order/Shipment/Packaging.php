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
    /** @var  ModuleConfigInterface */
    private $moduleConfig;

    protected $_scopeConfig;

    /**
     * Packaging constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param GenericInterface $sourceSizeModel
     * @param Registry $coreRegistry
     * @param CarrierFactory $carrierFactory
     * @param array $data
     * @param ModuleConfigInterface $moduleConfig
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        GenericInterface $sourceSizeModel,
        Registry $coreRegistry,
        CarrierFactory $carrierFactory,
        ModuleConfigInterface $moduleConfig,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->_scopeConfig = $scopeConfig;
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
        $scopeId = $this->getShipment()->getStoreId();

        return strtoupper($this->_scopeConfig->getValue(
            'general/locale/weight_unit',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $scopeId
        ));
    }

    /**
     * @return bool
     */
    public function isMetricUnit()
    {
        $unit = $this->getStoreWeightUnit();
        return $unit != \Zend_Measure_Weight::LBS;
    }
}
