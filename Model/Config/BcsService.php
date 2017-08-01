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

namespace Dhl\Shipping\Model\Config;

use Dhl\Shipping\Service\ServiceCollectionFactory;
use Dhl\Shipping\Service;

/**
 * BcsService
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class BcsService
{
    const CONFIG_XML_FIELD_PREFERREDDAY_HANDLING_FEE = 'carriers/dhlshipping/service_preferredday_handling_fee';
    const CONFIG_XML_FIELD_PREFERREDDAY_HANDLING_FEE_TEXT = 'carriers/dhlshipping/service_preferredday_handling_fee_text';
    const CONFIG_XML_FIELD_PREFERREDTIME_HANDLING_FEE = 'carriers/dhlshipping/service_preferredtime_handling_fee';
    const CONFIG_XML_FIELD_PREFERREDTIME_HANDLING_FEE_TEXT = 'carriers/dhlshipping/service_preferredtime_handling_fee_text';
    const CONFIG_XML_FIELD_CUTOFFTIME = 'carriers/dhlshipping/service_cutoff_time';
    const CONFIG_XML_FIELD_PREFERREDLOCATION_PLACEHOLDER = 'carriers/dhlshipping/service_preferredlocation_placeholder';
    const CONFIG_XML_FIELD_PREFERREDNEIGHBOUR_PLACEHOLDER = 'carriers/dhlshipping/service_preferredneighbour_placeholder';

    const CONFIG_XML_PATH_AUTOCREATE_VISUALCHECKOFAGE = 'carriers/dhlshipping/shipment_autocreate_service_visualcheckofage';
    const CONFIG_XML_PATH_AUTOCREATE_RETURNSHIPMENT   = 'carriers/dhlshipping/shipment_autocreate_service_returnshipment';
    const CONFIG_XML_PATH_AUTOCREATE_INSURANCE        = 'carriers/dhlshipping/shipment_autocreate_service_insurance';
    const CONFIG_XML_PATH_AUTOCREATE_BULKYGOODS       = 'carriers/dhlshipping/shipment_autocreate_service_bulkygoods';

    /**
     * @var ConfigAccessorInterface
     */
    private $configAccessor;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var Service\ServiceFactory
     */
    private $serviceFactory;

    /**
     * BcsService constructor.
     * @param ConfigAccessorInterface $configAccessor
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param Service\ServiceFactory $serviceFactory
     */
    public function __construct(
        ConfigAccessorInterface $configAccessor,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        Service\ServiceFactory $serviceFactory
    ) {
        $this->configAccessor = $configAccessor;
        $this->pricingHelper =  $pricingHelper;
        $this->serviceFactory = $serviceFactory;
    }

    private function isServiceEnabled($code, $store = null)
    {
        $path = strtolower("carriers/dhlshipping/service_{$code}_enabled");
        return (bool)$this->configAccessor->getConfigValue($path, $store);
    }

    /**
     * Load all DHL additional service models.
     *
     * @param mixed $store
     *
     * @return Service\ServiceCollection
     */
    public function getServices($store = null)
    {
        $services = [];
        $serviceCodes = [
            // customer/checkout services
            Service\PreferredDay::CODE => true,
            Service\PreferredTime::CODE => true,
            Service\PreferredLocation::CODE => true,
            Service\PreferredNeighbour::CODE => true,
            Service\ParcelAnnouncement::CODE => true,
            // merchant/admin services
            Service\VisualCheckOfAge::CODE => false,
            Service\ReturnShipment::CODE => false,
            Service\Insurance::CODE => false,
            Service\BulkyGoods::CODE => false,
        ];

        foreach ($serviceCodes as $serviceCode => $isConfigurable) {
            if (!$isConfigurable || $this->isServiceEnabled($serviceCode, $store)) {
                $services[$serviceCode] = Service\ServiceFactory::get($serviceCode);
            }
        }

        return Service\ServiceCollection::fromArray($services);
    }

    /**
     * Obtain preferred day handling fee from config.
     *
     * @param null $store
     * @return int
     */
    public function getPrefDayFee($store = null)
    {
        return (float) $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PREFERREDDAY_HANDLING_FEE, $store);
    }

    /**
     * Obtain prefered time handling fees from config.
     *
     * @param null $store
     * @return int
     */
    public function getPrefTimeFee($store = null)
    {
        return (float) $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PREFERREDTIME_HANDLING_FEE, $store);
    }

    /**
     * Obtain pref day handling fee text from config.
     *
     * @param null $store
     * @return string
     */
    public function getPrefDayHandlingFeeText($store = null)
    {
        $text = '';
        $fee  = $this->getPrefDayFee($store);
        if ($fee > 0) {
            $formatedFee = $this->pricingHelper->currency($fee, true, false);
            $text = str_replace(
                '$1',
                '<b>' .$formatedFee . '</b>',
                $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PREFERREDDAY_HANDLING_FEE_TEXT, $store)
            );
        }

        return $text;
    }

    /**
     * Obtain pref time handling fee text from config.
     *
     * @param null $store
     * @return string
     */
    public function getPrefTimeHandlingFeeText($store = null)
    {
        $text = '';
        $fee  = $this->getPrefTimeFee($store);
        if ($fee > 0) {
            $formatedFee = $this->pricingHelper->currency($this->getPrefTimeFee($store), true, false);
            $text = str_replace(
                '$1',
                '<b>' .$formatedFee . '</b>',
                $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PREFERREDTIME_HANDLING_FEE_TEXT, $store)
            );
        }

        return $text;
    }
}
