<?php

namespace Dhl\Shipping\Model\Service;

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Api\ServicePoolInterface;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\Service\VisualCheckOfAge as VisualCheckOfAgeOptions;
use Dhl\Shipping\Model\Config\ConfigAccessorInterface;
use Dhl\Shipping\Model\Config\ServiceConfigInterface;
use Dhl\Shipping\Service\Bcs\BulkyGoods;
use Dhl\Shipping\Service\Bcs\Insurance;
use Dhl\Shipping\Service\Bcs\ParcelAnnouncement;
use Dhl\Shipping\Service\Bcs\PreferredDay;
use Dhl\Shipping\Service\Bcs\PreferredLocation;
use Dhl\Shipping\Service\Bcs\PreferredNeighbour;
use Dhl\Shipping\Service\Bcs\PreferredTime;
use Dhl\Shipping\Service\Bcs\PrintOnlyIfCodeable;
use Dhl\Shipping\Service\Bcs\ReturnShipment;
use Dhl\Shipping\Service\Bcs\VisualCheckOfAge;
use Dhl\Shipping\Webservice\ParcelManagement;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class ServiceConfig
{
    /**
     * @var ConfigAccessorInterface
     */
    private $configAccessor;

    /**
     * @var ParcelManagement
     */
    private $parcelManagement;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ServiceConfigInterface
     */
    private $serviceConfig;

    /**
     * @var StartDate
     */
    private $startDateModel;

    /**
     * ServiceConfig constructor.
     * @param ParcelManagement $parcelManagement
     * @param ConfigAccessorInterface $configAccessor
     * @param CheckoutSession $checkoutSession
     * @param DateTimeFactory $dateTimeFactory
     * @param ServiceConfigInterface $serviceConfig
     * @param StartDate $startDate
     */
    public function __construct(
        ParcelManagement $parcelManagement,
        ConfigAccessorInterface $configAccessor,
        CheckoutSession $checkoutSession,
        DateTimeFactory $dateTimeFactory,
        ServiceConfigInterface $serviceConfig,
        StartDate $startDate
    ) {
        $this->parcelManagement = $parcelManagement;
        $this->configAccessor = $configAccessor;
        $this->checkoutSession = $checkoutSession;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->serviceConfig = $serviceConfig;
        $this->startDateModel = $startDate;
    }

    /**
     * @param null $store
     * @return array
     */
    public function getServiceSettings($store = null)
    {
        $preferredDayCode = PreferredDay::CODE;
        $preferredDayEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower($preferredDayCode) . '_enabled',
            $store
        );
        $preferredDayConfig = [
            ServiceSettingsInterface::NAME => 'Preferred Day',
            ServiceSettingsInterface::IS_ENABLED => $preferredDayEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::SORT_ORDER => 10,
            ServiceSettingsInterface::OPTIONS => $this->getPreferredDayOptions()
        ];

        $bulkyGoodsConfig = [
            ServiceSettingsInterface::NAME => 'Bulky Goods', // display name
            ServiceSettingsInterface::IS_ENABLED => true, // general availability of service
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false, // customer can select service
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true, // merchant can select service
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower(BulkyGoods::CODE),
                $store
            ), // by default, service is selected for shipment order
            ServiceSettingsInterface::SORT_ORDER => 100,
            ServiceSettingsInterface::OPTIONS => [], // possible service properties
        ];

        $codConfig = [
            ServiceSettingsInterface::NAME => 'Cash On Delivery',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => false,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 110,
        ];

        $insuranceConfig =  [
            ServiceSettingsInterface::NAME => 'Additional Insurance',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower(Insurance::CODE),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 120,
            ServiceSettingsInterface::PROPERTIES => [
                Insurance::PROPERTY_CURRENCY_CODE => $this->configAccessor->getConfigValue(
                    'currency/options/base',
                    $store
                ),
            ],
        ];
        $parcelAnnouncementEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower(ParcelAnnouncement::CODE) . '_enabled',
            $store
        );
        $parcelAnnouncementConfig = [
            ServiceSettingsInterface::NAME => 'Parcel Announcement',
            ServiceSettingsInterface::IS_ENABLED => $parcelAnnouncementEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 30,
        ];


        $preferredLocationEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower(PreferredLocation::CODE) . '_enabled',
            $store
        );
        $preferredLocationConfig = [
            ServiceSettingsInterface::NAME => 'Preferred Location',
            ServiceSettingsInterface::IS_ENABLED => $preferredLocationEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 40,
        ];

        $preferredNeighbourEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower(PreferredNeighbour::CODE) . '_enabled',
            $store
        );
        $preferredNeighbourConfig = [
            ServiceSettingsInterface::NAME => 'Preferred Neighbour',
            ServiceSettingsInterface::IS_ENABLED => $preferredNeighbourEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 50,
        ];

        $preferredTimeEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower(PreferredTime::CODE) . '_enabled',
            $store
        );
        $preferredTimeConfig = [
            ServiceSettingsInterface::NAME => 'Preferred Time',
            ServiceSettingsInterface::IS_ENABLED => $preferredTimeEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::SORT_ORDER => 20,
            ServiceSettingsInterface::OPTIONS => $this->getPreferredTimeOptions()
        ];

        $printOnlyIfCodeableConfig = [
            ServiceSettingsInterface::NAME => 'Print Only If Codeable',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower(PrintOnlyIfCodeable::CODE),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 130,
        ];

        $returnShipmentConfig = [
            ServiceSettingsInterface::NAME => 'Return Shipment',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower(ReturnShipment::CODE),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 140,
        ];

        $visualCheckOfAgeDefault = $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/shipment_service_' . strtolower(VisualCheckOfAge::CODE),
            $store
        );
        $visualCheckOfAgeConfig = [
            ServiceSettingsInterface::NAME => 'Visual Check Of Age',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $visualCheckOfAgeDefault,
            ServiceSettingsInterface::OPTIONS => $this->getVisualCheckOfAgeOptions(),
            ServiceSettingsInterface::PROPERTIES => [
                VisualCheckOfAge::PROPERTY_AGE => $visualCheckOfAgeDefault,
            ],
            ServiceSettingsInterface::SORT_ORDER => 150,
        ];

        return [
            BulkyGoods::CODE => $bulkyGoodsConfig,
            ServicePoolInterface::SERVICE_COD_CODE => $codConfig,
            ServicePoolInterface::SERVICE_INSURANCE_CODE => $insuranceConfig,
            ParcelAnnouncement::CODE => $parcelAnnouncementConfig,
            PreferredDay::CODE => $preferredDayConfig,
            PreferredLocation::CODE => $preferredLocationConfig,
            PreferredNeighbour::CODE => $preferredNeighbourConfig,
            PreferredTime::CODE => $preferredTimeConfig,
            PrintOnlyIfCodeable::CODE => $printOnlyIfCodeableConfig,
            ReturnShipment::CODE => $returnShipmentConfig,
            VisualCheckOfAge::CODE => $visualCheckOfAgeConfig,
        ];

    }

    /**
     * @return string[]
     */
    private function getPreferredTimeOptions(): array
    {
        try {
            $startDate = $this->getStartDate();
            $zipCode = $this->getZipCode();
            // options from the api
            $options = $this->parcelManagement->getPreferredDayOptions($startDate, $zipCode);
        } catch (\Exception $e) {
            $options = [];
        }

        return $options;
    }

    /**
     * @return array
     */
    private function getPreferredDayOptions()
    {
        try {
            $startDate = $this->getStartDate();
            $zipCode = $this->getZipCode();
            // options from the api
            $options = $this->parcelManagement->getPreferredDayOptions($startDate, $zipCode);
        } catch (\Exception $e) {
            $options = [];
        }

        //@Todo: prepare options structure as needed in service settings
        return $options;
    }

    /**
     * @return string[]
     */
    public function getVisualCheckOfAgeOptions(): array
    {
        return [
            [
                'label' => VisualCheckOfAgeOptions::OPTION_A16,
                'value' => VisualCheckOfAgeOptions::OPTION_A16,
            ],
            [
                'label' => VisualCheckOfAgeOptions::OPTION_A18,
                'value' => VisualCheckOfAgeOptions::OPTION_A18,
            ],
        ];
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    private function getStartDate()
    {
        $storeId = $this->checkoutSession->getQuote()->getStoreId();
        $dateModel = $this->dateTimeFactory->create();
        $noDropOffDays = $this->serviceConfig->getExcludedDropOffDays($storeId);
        $cutOffTime = $this->serviceConfig->getCutOffTime($storeId);
        $cutOffTime = $dateModel->gmtTimestamp(str_replace(',', ':', $cutOffTime));
        $currentDate = $dateModel->gmtDate("Y-m-d H:i:s");
        $startDate = $this->startDateModel->getStartdate($currentDate, $cutOffTime, $noDropOffDays);


        return new \DateTime($startDate);
    }

    /**
     * @return string
     */
    private function getZipCode()
    {

        return '04229';


        //@todo fix getting postlcode -
        $quote = $this->checkoutSession->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        return $shippingAddress->getPostcode();
    }
}