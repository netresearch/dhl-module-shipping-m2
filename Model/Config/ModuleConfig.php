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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Config;

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterfaceFactory;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\Service\VisualCheckOfAge as VisualCheckOfAgeOptions;
use Dhl\Shipping\Service\Bcs\BulkyGoods;
use Dhl\Shipping\Service\Bcs\Cod;
use Dhl\Shipping\Service\Bcs\Insurance;
use Dhl\Shipping\Service\Bcs\ParcelAnnouncement;
use Dhl\Shipping\Service\Bcs\PreferredDay;
use Dhl\Shipping\Service\Bcs\PreferredLocation;
use Dhl\Shipping\Service\Bcs\PreferredNeighbour;
use Dhl\Shipping\Service\Bcs\PreferredTime;
use Dhl\Shipping\Service\Bcs\PrintOnlyIfCodeable;
use Dhl\Shipping\Service\Bcs\ReturnShipment;
use Dhl\Shipping\Service\Bcs\VisualCheckOfAge;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\ApiType;
use Dhl\Shipping\Util\ShippingProductsInterface;
use Dhl\Shipping\Util\ShippingRoutesInterface;
use \Magento\Shipping\Model\Config as ShippingConfig;
use Dhl\Shipping\Util\ShippingRoutes\RoutesInterface;

/**
 * ModuleConfig
 *
 * @package  Dhl\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ModuleConfig implements ModuleConfigInterface
{

    /**
     * @var ConfigAccessorInterface
     */
    private $configAccessor;

    /**
     * @var RoutesInterface
     */
    private $routeConfig;

    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * @var ServiceSettingsInterfaceFactory
     */
    private $serviceSettingsFactory;

    /**
     * ModuleConfig constructor.
     *
     * @param ConfigAccessorInterface $configAccessor
     * @param RoutesInterface $routeConfig
     * @param ShippingProductsInterface $shippingProducts
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     */
    public function __construct(
        ConfigAccessorInterface $configAccessor,
        RoutesInterface $routeConfig,
        ShippingProductsInterface $shippingProducts,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory
    ) {
        $this->configAccessor = $configAccessor;
        $this->routeConfig = $routeConfig;
        $this->shippingProducts = $shippingProducts;
        $this->serviceSettingsFactory = $serviceSettingsFactory;
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

        $isEnabled = $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_LOGGING_ENABLED);
        $isLevelEnabled = ($this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_LOG_LEVEL) <= $level);

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
        return (bool)$this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_MODE, $store);
    }

    /**
     * Obtain shipper country from shipping origin configuration.
     *
     * @param mixed $store
     * @return string
     */
    public function getShipperCountry($store = null)
    {
        $country = $this->configAccessor->getConfigValue(ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, $store);
        return $country;
    }

    /**
     * @param mixed $store
     * @return string[]
     */
    public function getEuCountryList($store = null)
    {
        $euCountries = $this->configAccessor->getConfigValue(
            \Magento\Shipping\Helper\Carrier::XML_PATH_EU_COUNTRIES_LIST,
            $store
        );
        return explode(',', $euCountries);
    }

    /**
     * Obtain the shipping method that should be processed with DHL Shipping.
     *
     * @param mixed $store
     * @return string[]
     */
    public function getShippingMethods($store = null)
    {
        $shippingMethods = $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DHLMETHODS, $store);
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
        $codPaymentMethods = $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_CODMETHODS, $store);
        if (empty($codPaymentMethods)) {
            $codPaymentMethods = [];
        } else {
            $codPaymentMethods = explode(',', $codPaymentMethods);
        }

        return $codPaymentMethods;
    }

    /**
     * Obtain the default product setting. This is used to highlight one
     * shipping product in case multiple products apply to the current route.
     *
     * @param string $recipientCountry
     * @param mixed $store
     * @return string
     */
    public function getDefaultProduct($recipientCountry, $store = null)
    {
        $defaultProducts = json_decode(
            $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_PRODUCTS, $store),
            true
        );
        if (isset($defaultProducts[$recipientCountry])) {
            $defaultProduct = $defaultProducts[$recipientCountry];
        } elseif (in_array($recipientCountry, $this->getEuCountryList())
                && isset($defaultProducts[RoutesInterface::REGION_EU])
        ) {
            $defaultProduct = $defaultProducts[RoutesInterface::REGION_EU];
        } else {
            $defaultProduct = $defaultProducts[RoutesInterface::REGION_INTERNATIONAL];
        }
        return $defaultProduct;
    }

    /**
     * Check if the given origin/destination combination can be processed with DHL Shipping.
     *
     * @param string $destinationCountryId
     * @param mixed $store
     * @return bool
     */
    public function canProcessRoute($destinationCountryId, $store = null)
    {
        $originCountryId = $this->getShipperCountry($store);
        $euCountries = $this->getEuCountryList($store);

        return $this->routeConfig->canProcessRoute($originCountryId, $destinationCountryId, $euCountries);
    }

    /**
     * Check if the given shipping method should be processed with DHL Shipping.
     *
     * @param string $shippingMethod
     * @param mixed $store
     * @return bool
     */
    public function canProcessMethod($shippingMethod, $store = null)
    {
        $configuredMethods = $this->getShippingMethods($store);
        foreach ($configuredMethods as $method) {
            if (strpos($shippingMethod, $method) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current order can be shipped with DHL Shipping.
     *
     * @param string $shippingMethod
     * @param string $destCountryId
     * @param mixed $store
     * @return bool
     */
    public function canProcessShipping($shippingMethod, $destCountryId, $store = null)
    {
        return $this->canProcessMethod($shippingMethod, $store) && $this->canProcessRoute($destCountryId, $store);
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

    /**
     * Get Eu Countries.
     *
     * @param $storeId
     * @return array
     */
    public function getEuCountries($storeId)
    {
        $euCountries = explode(
            ',',
            $this->configAccessor->getConfigValue(
                \Magento\Shipping\Helper\Carrier::XML_PATH_EU_COUNTRIES_LIST,
                $storeId
            )
        );

        return $euCountries;
    }

    /**
     * @param int $destinationCountryId
     * @param int|null $storeId
     * @return bool
     */
    public function isCrossBorderRoute($destinationCountryId, $storeId = null)
    {
        return $this->routeConfig->isCrossBorderRoute(
            $this->getShipperCountry($storeId),
            $destinationCountryId,
            $this->getEuCountries($storeId)
        );
    }

    /**
     * @param mixed $store
     * @return \Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface[]
     */
    public function getServiceSettings($store = null)
    {
        $bulkyGoodsCode = BulkyGoods::CODE;
        $bulkyGoodsConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Bulky Goods', // display name
            ServiceSettingsInterface::IS_ENABLED => true, // general availability of service
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false, // customer can select service
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true, // merchant can select service
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($bulkyGoodsCode),
                $store
            ), // by default, service is selected for shipment order
            ServiceSettingsInterface::OPTIONS => [], // possible service properties
        ]);

        $codCode = Cod::CODE;
        $codConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Cash On Delivery',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => false,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
        ]);

        $insuranceCode = Insurance::CODE;
        $insuranceConfig =  $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Additional Insurance',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => false,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($insuranceCode),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
        ]);

        $parcelAnnouncementCode = ParcelAnnouncement::CODE;
        $parcelAnnouncementConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Parcel Announcement',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($parcelAnnouncementCode),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
        ]);

        $preferredDayCode = PreferredDay::CODE;
        $preferredDayConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Preferred Day',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($preferredDayCode),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [], // todo(nr): init with possible values
        ]);

        $preferredLocationCode = PreferredLocation::CODE;
        $preferredLocationConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Preferred Location',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($preferredLocationCode),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
        ]);

        $preferredNeighbourCode = PreferredNeighbour::CODE;
        $preferredNeighbourConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Preferred Neighbour',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($preferredNeighbourCode),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
        ]);

        $preferredTimeCode = PreferredTime::CODE;
        $preferredTimeConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Preferred Time',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($preferredTimeCode),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [], // todo(nr): init with possible values
        ]);

        $printOnlyIfCodeableCode = PrintOnlyIfCodeable::CODE;
        $printOnlyIfCodeableConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Print Only If Codeable',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($printOnlyIfCodeableCode),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
        ]);

        $returnShipmentCode = ReturnShipment::CODE;
        $returnShipmentConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Return Shipment',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower($returnShipmentCode),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
        ]);

        $visualCheckOfAgeCode = VisualCheckOfAge::CODE;
        $visualCheckOfAgeDefault = $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/shipment_service_' . strtolower($visualCheckOfAgeCode),
            $store
        );
        $visualCheckOfAgeConfig = $this->serviceSettingsFactory->create([
            ServiceSettingsInterface::NAME => 'Visual Check Of Age',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $visualCheckOfAgeDefault,
            ServiceSettingsInterface::OPTIONS => [
                VisualCheckOfAgeOptions::OPTION_A16 => VisualCheckOfAgeOptions::OPTION_A16,
                VisualCheckOfAgeOptions::OPTION_A18 => VisualCheckOfAgeOptions::OPTION_A18,
            ],
            ServiceSettingsInterface::PROPERTIES => [
                VisualCheckOfAge::PROPERTY_AGE => $visualCheckOfAgeDefault,
            ],
        ]);

        $settings = [
            $bulkyGoodsCode => $bulkyGoodsConfig,
            $codCode => $codConfig,
            $insuranceCode => $insuranceConfig,
            $parcelAnnouncementCode => $parcelAnnouncementConfig,
            $preferredDayCode => $preferredDayConfig,
            $preferredLocationCode => $preferredLocationConfig,
            $preferredNeighbourCode => $preferredNeighbourConfig,
            $preferredTimeCode => $preferredTimeConfig,
            $printOnlyIfCodeableCode => $printOnlyIfCodeableConfig,
            $returnShipmentCode => $returnShipmentConfig,
            $visualCheckOfAgeCode => $visualCheckOfAgeConfig,
        ];

        return $settings;
    }

    /**
     * Get allowed order statuses for automatic shipment creation
     *
     * @param null $store
     * @return mixed
     */
    public function getAutoCreateOrderStatus($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTOCREATE_ORDER_STATUS, $store);
    }

    /**
     * Get Notify Customer config.
     *
     * @param null $store
     * @return bool
     */
    public function getAutoCreateNotifyCustomer($store = null)
    {
        return (bool) $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTOCREATE_NOTIFY_CUSTOMER, $store);
    }

    /**
     * Get canonical module version number string
     *
     * @param mixed $store
     * @return string
     */
    public function getModuleVersion($store = null)
    {
        return $this->configAccessor->getConfigValue(ModuleConfigInterface::CONFIG_XML_PATH_MODULE_VERSION, $store);
    }

    /**
     * Get default value of Place of Commital
     *
     * @param mixed $store
     * @return mixed|string
     */
    public function getDefaultPlaceOfCommital($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_PLACE_OF_COMMITAL, $store);
    }

    /**
     * Get the default value of Addtional Fee
     *
     * @param mixed $store
     * @return mixed
     */
    public function getDefaultAdditionalFee($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_ADDITIONAL_FEE, $store);
    }

    /**
     * Get the current Api Type
     *
     * @param mixed $store
     * @return mixed
     */
    public function getApiType($store = null)
    {
        $shippingOrigin = $this->getShipperCountry($store);

        switch ($shippingOrigin) {
            case 'DE':
            case 'AT':
                return ApiType::API_TYPE_BCS;
            default:
                return in_array($shippingOrigin, $this->shippingProducts->getAllCountries())
                    ? ApiType::API_TYPE_GLA
                    : ApiType::API_TYPE_NA;
        }
    }

    /**
     * Get the default value of Terms of Trade
     *
     * @param mixed $store
     * @return mixed
     */
    public function getTermsOfTrade($store = null)
    {
        $api = $this->getApiType($store);
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_TERM_OF_TRADE.'_'.$api, $store);
    }

    /**
     * Get the default value of Export Content Type
     *
     * @param null $store
     * @return mixed
     */
    public function getDefaultExportContentType($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_EXPORT_CONTENT_TYPE, $store);
    }

    /**
     * Get default value of Export Content Type Explanation if Content Type is 'Other'
     *
     * @param null $store
     * @return mixed
     */
    public function getDefaultExportContentTypeExplanation($store = null)
    {
        return $this->configAccessor->getConfigValue(
            self::CONFIG_XML_PATH_DEFAULT_EXPORT_CONTENT_TYPE_EXPLANATION,
            $store
        );
    }
}
