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

use Dhl\Shipping\Service;

/**
 * ServiceConfig
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceConfig implements ServiceConfigInterface
{
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
            Service\ParcelAnnouncement::CODE,
            Service\PreferredDay::CODE,
            Service\PreferredTime::CODE,
            Service\PreferredLocation::CODE,
            Service\PreferredNeighbour::CODE,
            // merchant/admin services
            Service\BulkyGoods::CODE,
            Service\Insurance::CODE,
            Service\PrintOnlyIfCodeable::CODE,
            Service\ReturnShipment::CODE,
            Service\VisualCheckOfAge::CODE,
        ];

        foreach ($serviceCodes as $serviceCode) {
            // read config
            $path = strtolower("carriers/dhlshipping/shipment_service_{$serviceCode}");
            $serviceValue = $this->configAccessor->getConfigValue($path, $store);

            $service = Service\ServiceFactory::get($serviceCode, $serviceValue);
            $services[$serviceCode] = $service;
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
