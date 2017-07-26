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

use Dhl\Shipping\Model\Shipping\Service\ServiceCollection;
use Dhl\Shipping\Model\Shipping\Service\ServiceCollectionFactory;
use Dhl\Shipping\Service;
use Dhl\Shipping\Webservice\ShippingInfo\Info;
use Magento\Framework\App\Area;
use Dhl\Shipping\Model\Shipping\Service\ServiceFactory;
use Dhl\Shipping\Model\Shipping\Service\ServiceInterface;
use DateTime;
use Dhl\Shipping\Util;
use Dhl\Shipping\Service\Filter;
use Dhl\Shipping\Util\Holidays as HolidayCheck;

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
    const CONFIG_XML_FIELD_PREFERREDDAY = 'service_preferredday_enabled';
    const CONFIG_XML_FIELD_PREFERREDDAY_HANDLING_FEE = 'service_preferredday_handling_fee';
    const CONFIG_XML_FIELD_PREFERREDDAY_HANDLING_FEE_TEXT = 'service_preferredday_handling_fee_text';
    const CONFIG_XML_FIELD_PREFERREDTIME = 'service_preferredtime_enabled';
    const CONFIG_XML_FIELD_PREFERREDTIME_HANDLING_FEE = 'service_preferredtime_handling_fee';
    const CONFIG_XML_FIELD_PREFERREDTIME_HANDLING_FEE_TEXT = 'service_preferredtime_handling_fee_text';
    const CONFIG_XML_FIELD_CUTOFFTIME = 'service_cutoff_time';
    const CONFIG_XML_FIELD_PREFERREDLOCATION = 'service_preferredlocation_enabled';
    const CONFIG_XML_FIELD_PREFERREDLOCATION_PLACEHOLDER = 'service_preferredlocation_placeholder';
    const CONFIG_XML_FIELD_PREFERREDNEIGHBOUR = 'service_preferredneighbour_enabled';
    const CONFIG_XML_FIELD_PREFERREDNEIGHBOUR_PLACEHOLDER = 'service_preferredneighbour_placeholder';
    const CONFIG_XML_FIELD_PACKSTATION = 'service_packstation_enabled';
    const CONFIG_XML_FIELD_PARCELANNOUNCEMENT = 'service_parcelannouncement_enabled';
    const CONFIG_XML_FIELD_VISUALCHECKOFAGE = 'service_visualcheckofage_enabled';
    const CONFIG_XML_FIELD_RETURNSHIPMENT = 'service_returnshipment_enabled';
    const CONFIG_XML_FIELD_INSURANCE = 'service_insurance_enabled';
    const CONFIG_XML_FIELD_BULKYGOODS = 'service_bulkygoods_enabled';

    const CONFIG_XML_PATH_AUTOCREATE_VISUALCHECKOFAGE = 'shipment_autocreate_service_visualcheckofage';
    const CONFIG_XML_PATH_AUTOCREATE_RETURNSHIPMENT   = 'shipment_autocreate_service_returnshipment';
    const CONFIG_XML_PATH_AUTOCREATE_INSURANCE        = 'shipment_autocreate_service_insurance';
    const CONFIG_XML_PATH_AUTOCREATE_BULKYGOODS       = 'shipment_autocreate_service_bulkygoods';

    /**
     * @var ConfigAccessorInterface
     */
    private $configAccessor;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var \Magento\Framework\Registry $registry
     */
    private $registry;

    /**
     * @var \Dhl\Shipping\Model\ShippingInfo\ShippingInfoRepositoryInterface
     */
    private $shippingInfoRepository;

    /**
     * @var ServiceCollectionFactory
     */
    private $serviceCollectionFactory;

    /**
     * @var Util\ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * @var Filter\ProductFilter
     */
    private $productFilter;

    /**
     * BcsService constructor.
     * @param ConfigAccessorInterface $configAccessor
     * @param ModuleConfigInterface $moduleConfig
     */
    public function __construct(
        ConfigAccessorInterface $configAccessor,
        ModuleConfigInterface $moduleConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        ServiceFactory $serviceFactory,
        \Magento\Framework\Registry $registry,
        \Dhl\Shipping\Model\ShippingInfo\ShippingInfoRepositoryInterface $shippingInfoRepository,
        ServiceCollectionFactory $serviceCollectionFactory,
        Util\ShippingProductsInterface $shippingProducts,
        Filter\ProductFilter $productFilter
    ) {
        $this->configAccessor = $configAccessor;
        $this->moduleConfig = $moduleConfig;
        $this->dateTime = $dateTime;
        $this->appState = $appState;
        $this->pricingHelper =  $pricingHelper;
        $this->serviceFactory = $serviceFactory;
        $this->registry = $registry;
        $this->shippingInfoRepository = $shippingInfoRepository;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->shippingProducts = $shippingProducts;
        $this->productFilter = $productFilter;
    }

    /**
     * @param mixed $store
     *
     * @return Service\PreferredDay
     */
    private function initPreferredDay($store = null)
    {
        $name              = __("Preferred day")  .  __(": Delivery at your preferred day");
        $isAvailable       = $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PREFERREDDAY, $store);
        $cutOffTime        = $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_CUTOFFTIME, $store);
        $isSelected        = false;
        $options           = [];
        $gmtSaveTimeFormat = "Y-m-d 12:00:00";

        $start      = $this->dateTime->gmtDate("Y-m-d H:i:s");
        $cutOffTime = $this->dateTime->gmtTimestamp(str_replace(',', ':', $cutOffTime));
        $startDate  = ($cutOffTime < $this->dateTime->gmtTimestamp($start)) ? 3 : 2;
        $endDate    = $startDate + 5;

        for ($i = $startDate; $i < $endDate; $i++) {
            $date      = new DateTime($start);
            $tmpDate   = $date->add(new \DateInterval("P{$i}D"));
            $checkDate = $tmpDate->format($gmtSaveTimeFormat);
            $tmpDate   = $tmpDate->format("Y-m-d");
            $disabled  = false;
            if (($this->dateTime->gmtDate('N', strtotime($checkDate)) == 7) || HolidayCheck::isHoliday($checkDate)) {
                $endDate++;
                $disabled = true;
            }

            $options[$tmpDate] = [
                    'value'    => $this->dateTime->gmtDate("d-", $checkDate) .
                        __($this->dateTime->gmtDate("D", $checkDate)),
                    'disabled' => $disabled
                ];
        }

        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->registry->registry('current_shipment');
        // Only for Backend rendering with selected da
        if ($shipment && $shipment->getShippingAddress()) {
            $serializedInfo = $this->shippingInfoRepository->getById($shipment->getShippingAddress()->getId());
            /** @var Info $versendenInfo */
            $versendenInfo = Info::fromJson($serializedInfo);

            if ($versendenInfo && $versendenInfo->getServices()->{Service\PreferredDay::CODE}
                && !array_key_exists($versendenInfo->getServices()->{Service\PreferredDay::CODE}, $options)
            ) {
                // Sanity check for invalid time formats
                try {
                    $selectedValue           = $versendenInfo->getServices()->{Service\PreferredDay::CODE};
                    $tmpDate                 = new DateTime($selectedValue);
                    $tmpDate                 = $this->dateTime->gmtDate($gmtSaveTimeFormat, $tmpDate->format($gmtSaveTimeFormat));
                    $options[$selectedValue] =
                        array(
                            'value'    => $this->dateTime->gmtDate("d-", $tmpDate) . __($this->dateTime->gmtDate("D", $tmpDate)),
                            'disabled' => false
                        );
                } catch (\Exception $e) { }
            }
        }

        return $this->serviceFactory->create(Service\PreferredDay::CODE, [$name, $isAvailable, $isSelected, $options]);
    }

    /**
     * @param mixed $store
     *
     * @return Service\PreferredTime
     */
    private function initPreferredTime($store = null)
    {
        $name        = __("Preferred time") . __(": Delivery during your preferred time slot");
        $isAvailable = $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PREFERREDTIME, $store);
        $isSelected  = false;
        $options     = [];

        if ($this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
            $options = $options + [
                    '10001200' => __('10 - 12*'),
                    '12001400' => __('12 - 14*'),
                    '14001600' => __('14 - 16*'),
                    '16001800' => __('16 - 18*'),
                ];
        }

        $options = $options + [
                '18002000' => __('18 - 20'),
                '19002100' => __('19 - 21'),
            ];

        $args = [
            $name,
            $isAvailable,
            $isSelected,
            $options
        ];

        return $this->serviceFactory->create(Service\PreferredTime::CODE, $args);
    }

    /**
     * @param mixed $store
     *
     * @return Service\PreferredLocation
     */
    private function initPreferredLocation($store = null)
    {
        $name        = __("Preferred location") . __(": Delivery to your preferred drop-off location");
        $isAvailable = $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PREFERREDLOCATION, $store);
        $isSelected  = false;
        $placeholder = $this->configAccessor->getConfigValue(
            self::CONFIG_XML_FIELD_PREFERREDLOCATION_PLACEHOLDER,
            $store
        );
        $placeholder = __($placeholder);
        $args = [
            $name,
            $isAvailable,
            $isSelected,
            $placeholder
        ];

        return $this->serviceFactory->create(Service\PreferredLocation::CODE, $args);
    }

    /**
     * @param mixed $store
     *
     * @return Service\PreferredNeighbour
     */
    private function initPreferredNeighbour($store = null)
    {
        $name        = __("Preferred neighbor") . __(": Delivery to a neighbor of your choice");
        $isAvailable = $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PREFERREDNEIGHBOUR, $store);
        $isSelected  = false;
        $placeholder = $this->configAccessor->getConfigValue(
            self::CONFIG_XML_FIELD_PREFERREDNEIGHBOUR_PLACEHOLDER,
            $store
        );
        $placeholder = __($placeholder);
        $args = [
            $name,
            $isAvailable,
            $isSelected,
            $placeholder
        ];

        return $this->serviceFactory->create(Service\PreferredNeighbour::CODE, $args);
    }

    /**
     * @param mixed $store
     *
     * @return Service\ParcelAnnouncement
     */
    private function initParcelAnnouncement($store = null)
    {
        $name        = __("Parcel announcement");
        $isAvailable = $this->configAccessor->getConfigValue(self::CONFIG_XML_FIELD_PARCELANNOUNCEMENT, $store);
        $isSelected  = false;

        $args = [
            $name,
            $isAvailable,
            $isSelected
        ];

        return $this->serviceFactory->create(Service\ParcelAnnouncement::CODE, $args);
    }

    /**
     * @return Service\VisualCheckOfAge
     */
    private function initVisualCheckOfAge()
    {
        $name        = __("Visual Check of Age");
        $isAvailable = true;
        $isSelected  = false;
        $options     = [
            Service\VisualCheckOfAge::A16 => Service\VisualCheckOfAge::A16,
            Service\VisualCheckOfAge::A18 => Service\VisualCheckOfAge::A18,
        ];

        $args = [
            $name,
            $isAvailable,
            $isSelected,
            $options
        ];

        return $this->serviceFactory->create(Service\VisualCheckOfAge::CODE, $args);
    }

    /**
     * @return Service\ReturnShipment
     */
    private function initReturnShipment()
    {
        $name        = __("Return Shipment");
        $isAvailable = true;
        $isSelected  = false;

        $args = [
            $name,
            $isAvailable,
            $isSelected
        ];

        return $this->serviceFactory->create(Service\ReturnShipment::CODE, $args);
    }

    /**
     * @return Service\Insurance
     */
    private function initInsurance()
    {
        $name        = __("Additional Insurance");
        $isAvailable = true;
        $isSelected  = false;

        $args = [
            $name,
            $isAvailable,
            $isSelected
        ];

        return $this->serviceFactory->create(Service\Insurance::CODE, $args);
    }

    /**
     * @return Service\BulkyGoods
     */
    private function initBulkyGoods()
    {
        $name        = __("Bulky Goods");
        $isAvailable = true;
        $isSelected  = false;

        $args = [
            $name,
            $isAvailable,
            $isSelected
        ];

        return $this->serviceFactory->create(Service\BulkyGoods::CODE, $args);
    }

    /**
     * Load all DHL additional service models.
     *
     * @param mixed $store
     *
     * @return ServiceCollection
     */
    public function getServices($store = null)
    {
        $services = [];

        // customer/checkout services
        $services[] = $this->initPreferredDay($store);
        $services[] = $this->initPreferredTime($store);
        $services[] = $this->initPreferredLocation($store);
        $services[] = $this->initPreferredNeighbour($store);
        $services[] = $this->initParcelAnnouncement($store);
        // merchant/admin services
        $services[] = $this->initVisualCheckOfAge();
        $services[] = $this->initReturnShipment();
        $services[] = $this->initInsurance();
        $services[] = $this->initBulkyGoods();

        $services = array_reduce($services, function ($carry, ServiceInterface $service) {
            $carry[$service->getCode()] = $service;
            return $carry;
        }, []);

        return $this->serviceCollectionFactory->create($services);
    }

    /**
     * Obtain the service objects that are enabled via module configuration.
     *
     * @param mixed $store
     *
     * @return ServiceCollection
     */
    public function getEnabledServices($store = null)
    {
        $services = $this->getServices($store);

        $items = $services->filter(
            function (ServiceInterface $service) {
                return (bool)$service->isEnabled();
            }
        );

        return $items;
    }

    /**
     * Obtain the service objects that are
     * - enabled via module configuration and
     * - selected for autocreate labels.
     *
     * @param mixed $store
     *
     * @return ServiceCollection
     */
    public function getAutoCreateServices($store = null)
    {
        // read autocreate service values from config
        $ageCheckValue       = $this->configAccessor->getConfigValue(
            self::CONFIG_XML_PATH_AUTOCREATE_VISUALCHECKOFAGE,
            $store
        );
        $returnShipmentValue = $this->configAccessor->getConfigValue(
            self::CONFIG_XML_PATH_AUTOCREATE_RETURNSHIPMENT,
            $store
        );
        $insuranceValue      = $this->configAccessor->getConfigValue(
            self::CONFIG_XML_PATH_AUTOCREATE_INSURANCE,
            $store
        );
        $bulkyGoodsValue     = $this->configAccessor->getConfigValue(
            self::CONFIG_XML_PATH_AUTOCREATE_BULKYGOODS,
            $store
        );

        $autoCreateValues = [
            Service\VisualCheckOfAge::CODE    => $ageCheckValue,
            Service\ReturnShipment::CODE      => $returnShipmentValue,
            Service\Insurance::CODE           => $insuranceValue,
            Service\BulkyGoods::CODE          => $bulkyGoodsValue,
        ];

        // obtain all enabled services
        $services = $this->getEnabledServices($store);

        // skip services disabled for auto creation
        $items = $services->filter(
            function (ServiceInterface $service) use ($autoCreateValues) {
                return (isset($autoCreateValues[$service->getCode()]) && $autoCreateValues[$service->getCode()]);
            }
        );

        // set autocreate service details to remaining services
        /** @var ServiceInterface $service */
        foreach ($items as $service) {
            if (isset($autoCreateValues[$service->getCode()])) {
                $service->setValue($autoCreateValues[$service->getCode()]);
            }
        }

        return $items;
    }

    /**
     * Obtain the service objects that are enabled via module configuration and
     * applicable to the given order parameters.
     *
     * @param string $shipperCountry
     * @param string $recipientCountry
     * @param bool   $isPostalFacility
     * @param bool   $onlyCustomerServices
     * @param mixed  $store
     *
     * @return ServiceCollection
     */
    public function getAvailableServices(
        $shipperCountry,
        $recipientCountry,
        $isPostalFacility,
        $onlyCustomerServices = false,
        $store = null
    ) {
        $services = $this->getEnabledServices($store);
        $euCountries = $this->moduleConfig->getEuCountryList($store);
        $shippingProducts = $this->shippingProducts->getApplicableCodes(
            $shipperCountry,
            $recipientCountry,
            $euCountries
        );

        //@FIXME: implement proper filter
//        $productFilter = $this->productFilter;
//        $services = $services->filter(function (ServiceInterface $service) use ($productFilter) {
//           return $productFilter->isAllowed($service);
//        });


        return $services;
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
