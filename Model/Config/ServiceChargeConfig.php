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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * ServiceChargeConfig.
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceChargeConfig
{
    const CONFIG_PATH = 'carriers/dhlshipping/checkout_service_settings/';
    const CONFIG_XML_FIELD_PREFERRED_DAY_CHARGE       = self::CONFIG_PATH . 'preferred_day_charge';
    const CONFIG_XML_FIELD_PREFERRED_TIME_CHARGE      = self::CONFIG_PATH . 'preferred_time_charge';
    const CONFIG_XML_FIELD_COMBINED_CHARGE            = self::CONFIG_PATH . 'combined_charge';
    const CONFIG_XML_FIELD_PREFERRED_DAY_CHARGE_TEXT  = self::CONFIG_PATH . 'service_preferredday_charge_text';
    const CONFIG_XML_FIELD_PREFERRED_TIME_CHARGE_TEXT = self::CONFIG_PATH . 'service_preferredtime_charge_text';
    const CONFIG_XML_FIELD_COMBINED_CHARGE_TEXT       = self::CONFIG_PATH . 'service_combined_charge_text';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var PricingHelper
     */
    private $pricingHelper;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param PricingHelper $pricingHelper
     */
    public function __construct(ScopeConfigInterface $scopeConfig, PricingHelper $pricingHelper)
    {
        $this->scopeConfig = $scopeConfig;
        $this->pricingHelper = $pricingHelper;
    }

    /**
     * @param null|string $scopeId
     * @return float
     */
    public function getPreferredDayCharge($scopeId = null)
    {
        return (float) $this->scopeConfig->getValue(
            self::CONFIG_XML_FIELD_PREFERRED_DAY_CHARGE,
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    /**
     * @param null|string $scopeId
     * @return float
     */
    public function getPreferredTimeCharge($scopeId = null)
    {
        return (float) $this->scopeConfig->getValue(
            self::CONFIG_XML_FIELD_PREFERRED_TIME_CHARGE,
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    /**
     * @param null|string $scopeId
     * @return float
     */
    public function getCombinedCharge($scopeId = null)
    {
        return (float) $this->scopeConfig->getValue(
            self::CONFIG_XML_FIELD_COMBINED_CHARGE,
            ScopeInterface::SCOPE_STORE,
            $scopeId
        );
    }

    /**
     * Obtain pref day handling fee text from config.
     *
     * @param null $store
     * @return string
     */
    public function getPrefDayHandlingChargeText($store = null)
    {
        $text = '';
        $fee  = $this->getPreferredDayCharge($store);
        if ($fee > 0) {
            $formatedFee = $this->pricingHelper->currency($fee, true, false);
            $text = str_replace(
                '$1',
                '<b>' .$formatedFee . '</b>',
                $this->scopeConfig->getValue(
                    self::CONFIG_XML_FIELD_PREFERRED_DAY_CHARGE_TEXT,
                    ScopeInterface::SCOPE_STORE,
                    $store
                )
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
    public function getPrefTimeHandlingChargeText($store = null)
    {
        $text = '';
        $fee  = $this->getPreferredTimeCharge($store);
        if ($fee > 0) {
            $formatedFee = $this->pricingHelper->currency($fee, true, false);
            $text = str_replace(
                '$1',
                '<b>' .$formatedFee . '</b>',
                $this->scopeConfig->getValue(
                    self::CONFIG_XML_FIELD_PREFERRED_TIME_CHARGE_TEXT,
                    ScopeInterface::SCOPE_STORE,
                    $store
                )
            );
        }

        return $text;
    }

    /**
     * Obtain combined pref day and time handling fee text from config.
     *
     * @param null $store
     * @return string
     */
    public function getCombinedChargeText($store = null)
    {
        $text = '';
        $fee = $this->getCombinedCharge($store);
        if ($fee > 0) {
            $formatedFee = $this->pricingHelper->currency($fee, true, false);
            $text = str_replace(
                '$1',
                '<strong>' . $formatedFee . '</strong>',
                $this->scopeConfig->getValue(
                    self::CONFIG_XML_FIELD_COMBINED_CHARGE_TEXT,
                    ScopeInterface::SCOPE_STORE,
                    $store
                )
            );
        }

        return $text;
    }
}
