<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Model;

use \Dhl\Versenden\Api\ConfigInterface;
use \Magento\Framework\App\Config\Storage\WriterInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Config
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Config implements ConfigInterface
{
    const CONFIG_XML_PATH_TITLE = 'carriers/dhlversenden/title';

    const CONFIG_XML_PATH_LOGGING_ENABLED  = 'carriers/dhlversenden/logging_enabled';
    const CONFIG_XML_PATH_LOG_LEVEL = 'carriers/dhlversenden/log_level';

    const CONFIG_XML_PATH_SANDBOX_MODE = 'carriers/dhlversenden/sandbox_mode';

    const CONFIG_XML_PATH_DHLMETHODS = 'carriers/dhlversenden/shipment_dhlmethods';
    const CONFIG_XML_PATH_CODMETHODS = 'carriers/dhlversenden/shipment_dhlcodmethods';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig  = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * Save config value to storage.
     *
     * @param string $path
     * @param string $value
     * @param mixed $scopeId
     */
    private function saveConfigValue($path, $value, $scopeId = 0)
    {
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if ($scopeId) {
            $scope = ScopeInterface::SCOPE_STORES;
            $scopeId = $this->storeManager->getStore($scopeId)->getId();
        }

        $this->configWriter->save($path, $value, $scope, $scopeId);
    }

    /**
     * Read config value from storage.
     *
     * @param $path
     * @param int $scopeId
     * @return mixed
     */
    private function getConfigValue($path, $scopeId = null)
    {
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if ($scopeId) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->getValue($path, $scope, $scopeId);
    }

    /**
     * Check if logging is enabled (global scope)
     *
     * @param int $level
     * @return bool
     */
    public function isLoggingEnabled($level = null)
    {
        $level = ($level === null) ? \Monolog\Logger::DEBUG : $level;

        $isEnabled = $this->getConfigValue(self::CONFIG_XML_PATH_LOGGING_ENABLED);
        $isLevelEnabled = ($this->getConfigValue(self::CONFIG_XML_PATH_LOG_LEVEL) <= $level);

        return ($isEnabled && $isLevelEnabled);
    }

    /**
     * Check if Sandbox mode is enabled in config.
     *
     * @param mixed $store
     * @return bool
     */
    public function isSandboxModeEnabled($store = null)
    {
        return (bool) $this->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_MODE, $store);
    }

    /**
     * Obtain shipper country from shipping origin configuration.
     *
     * @param mixed $store
     * @return string
     */
    public function getShipperCountry($store = null)
    {
        $shipperCountry = $this->getConfigValue(
            \Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID,
            $store
        );

        return $shipperCountry;
    }

    /**
     * @param mixed $store
     * @return string[]
     */
    public function getEuCountryList($store = null)
    {
        $euCountries = $this->getConfigValue(
            \Magento\Shipping\Helper\Carrier::XML_PATH_EU_COUNTRIES_LIST,
            $store
        );
        return explode(',', $euCountries);
    }

    /**
     * Obtain the shipping method that should be processed with DHL Versenden.
     *
     * @param mixed $store
     * @return string[]
     */
    public function getShippingMethods($store = null)
    {
        $shippingMethods = $this->getConfigValue(self::CONFIG_XML_PATH_DHLMETHODS, $store);
        if (empty($shippingMethods)) {
            $shippingMethods = [];
        } else {
            $shippingMethods = explode(',', $shippingMethods);
        }

        return $shippingMethods;
    }

    /**
     * Obtain the payment methods that should be treated as COD.
     *
     * @param mixed $store
     * @return string[]
     */
    public function getCodPaymentMethods($store = null)
    {
        $codPaymentMethods = $this->getConfigValue(self::CONFIG_XML_PATH_CODMETHODS, $store);
        if (empty($codPaymentMethods)) {
            $codPaymentMethods = [];
        } else {
            $codPaymentMethods = explode(',', $codPaymentMethods);
        }

        return $codPaymentMethods;
    }

    /**
     * Check if the given shipping method should be processed with DHL Versenden.
     *
     * @param string $shippingMethod
     * @param mixed $store
     * @return bool
     */
    public function canProcessMethod($shippingMethod, $store = null)
    {
        if (!in_array($this->getShipperCountry($store), ['AT', 'DE'])) {
            // shipper country is not supported, regardless of shipping method.
            return false;
        }

        return in_array($shippingMethod, $this->getShippingMethods($store));
    }

    /**
     * Check if the given payment method is cash on delivery.
     *
     * @param string $paymentMethod
     * @param mixed $store
     * @return bool
     */
    public function isCodPaymentMethod($paymentMethod, $store = null)
    {
        return in_array($paymentMethod, $this->getCodPaymentMethods($store));
    }
}
