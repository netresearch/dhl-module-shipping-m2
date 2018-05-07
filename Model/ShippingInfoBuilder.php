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
 * @package   Dhl\Shipping\Model
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model;

use Dhl\Shipping\Api\Data\ShippingInfoInterface;
use Dhl\Shipping\Api\Data\ShippingInfoInterfaceFactory;
use Dhl\Shipping\Api\Data\ShippingInfo\ReceiverInterface;
use Dhl\Shipping\Api\Data\ShippingInfo\ReceiverInterfaceFactory;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\AddressInterface;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\AddressInterfaceFactory;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\ContactInterface;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\ContactInterfaceFactory;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\PackstationInterface;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\PackstationInterfaceFactory;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\ParcelShopInterface;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\ParcelShopInterfaceFactory;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\PostfilialeInterface;
use Dhl\Shipping\Api\Data\ShippingInfo\Receiver\PostfilialeInterfaceFactory;
use Dhl\Shipping\Api\Data\ShippingInfo\ServiceInterface;
use Dhl\Shipping\Api\Data\ShippingInfo\ServiceInterfaceFactory;
use Dhl\Shipping\Api\Data\ShippingInfo\Service\ServicePropertyInterface;
use Dhl\Shipping\Api\Data\ShippingInfo\Service\ServicePropertyInterfaceFactory;
use Dhl\Shipping\Util\StreetSplitterInterface;
use Magento\Customer\Model\Address\AddressModelInterface;
use Magento\Directory\Model\CountryFactory;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Sales\Model\Order\Address as OrderAddress;

/**
 * ShippingInfoBuilder
 *
 * @category Dhl
 * @package  Dhl\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingInfoBuilder
{
    const SCHEMA_VERSION = '2.0';

    /**
     * @var mixed[]
     */
    private $info = [];

    /**
     * @var ShippingInfoInterfaceFactory
     */
    private $shippingInfoFactory;

    /**
     * @var ReceiverInterfaceFactory
     */
    private $receiverFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private $receiverAddressFactory;

    /**
     * @var ContactInterfaceFactory
     */
    private $receiverContactFactory;

    /**
     * @var PackstationInterfaceFactory
     */
    private $receiverPackstationFactory;

    /**
     * @var PostfilialeInterfaceFactory
     */
    private $receiverPostfilialeFactory;

    /**
     * @var ParcelShopInterfaceFactory
     */
    private $receiverParcelShopFactory;

    /**
     * @var ServiceInterfaceFactory
     */
    private $serviceFactory;

    /**
     * @var ServicePropertyInterfaceFactory
     */
    private $servicePropertyFactory;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var StreetSplitterInterface
     */
    private $streetSplitter;

    /**
     * ShippingInfoBuilder constructor.
     * @param ShippingInfoInterfaceFactory $shippingInfoFactory
     * @param ReceiverInterfaceFactory $receiverFactory
     * @param AddressInterfaceFactory $receiverAddressFactory
     * @param ContactInterfaceFactory $receiverContactFactory
     * @param PackstationInterfaceFactory $receiverPackstationFactory
     * @param PostfilialeInterfaceFactory $receiverPostfilialeFactory
     * @param ParcelShopInterfaceFactory $receiverParcelShopFactory
     * @param ServiceInterfaceFactory $serviceFactory
     * @param ServicePropertyInterfaceFactory $servicePropertyFactory
     * @param CountryFactory $countryFactory
     * @param StreetSplitterInterface $streetSplitter
     */
    public function __construct(
        ShippingInfoInterfaceFactory $shippingInfoFactory,
        ReceiverInterfaceFactory $receiverFactory,
        AddressInterfaceFactory $receiverAddressFactory,
        ContactInterfaceFactory $receiverContactFactory,
        PackstationInterfaceFactory $receiverPackstationFactory,
        PostfilialeInterfaceFactory $receiverPostfilialeFactory,
        ParcelShopInterfaceFactory $receiverParcelShopFactory,
        ServiceInterfaceFactory $serviceFactory,
        ServicePropertyInterfaceFactory $servicePropertyFactory,
        CountryFactory $countryFactory,
        StreetSplitterInterface $streetSplitter
    ) {
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->receiverFactory = $receiverFactory;
        $this->receiverAddressFactory = $receiverAddressFactory;
        $this->receiverContactFactory = $receiverContactFactory;
        $this->receiverPackstationFactory = $receiverPackstationFactory;
        $this->receiverPostfilialeFactory = $receiverPostfilialeFactory;
        $this->receiverParcelShopFactory = $receiverParcelShopFactory;
        $this->serviceFactory = $serviceFactory;
        $this->servicePropertyFactory = $servicePropertyFactory;
        $this->countryFactory = $countryFactory;
        $this->streetSplitter = $streetSplitter;
    }

    /**
     * Initialize builder with JSON representation.
     *
     * Apply framework decoder as soon as M2.1 compatibility gets dropped.
     * @see \Magento\Framework\Serialize\Serializer\Json::unserialize
     *
     * @param string $json
     * @return void
     */
    public function setInfo($json)
    {
        $info = json_decode($json, true);
        if (empty($info) || ($info[ShippingInfoInterface::SCHEMA_VERSION] !== self::SCHEMA_VERSION)) {
            $info = [];
        }

        $this->info = $info;
    }

    /**
     * Initialize builder with shipping address.
     *
     * @param AddressModelInterface|QuoteAddress|OrderAddress $shippingAddress
     * @return void
     */
    public function setShippingAddress(AddressModelInterface $shippingAddress)
    {
        $street = $shippingAddress->getStreetFull();
        $streetParts = $this->streetSplitter->splitStreet($street);

        $countryDirectory = $this->countryFactory->create();
        $countryDirectory->loadByCode($shippingAddress->getCountryId());

        $regionData = $countryDirectory->getLoadedRegionCollection()->walk('getName');
        if (!isset($regionData[$shippingAddress->getRegionId()])) {
            $region = $shippingAddress->getRegion();
        } else {
            $region = $regionData[$shippingAddress->getRegionId()];
        }

        $receiver = [
            ReceiverInterface::CONTACT => [
                ContactInterface::NAME => $shippingAddress->getName(),
                ContactInterface::COMPANY => $shippingAddress->getCompany(),
                ContactInterface::PHONE => $shippingAddress->getTelephone(),
                ContactInterface::EMAIL => $shippingAddress->getEmail(),

            ],
            ReceiverInterface::ADDRESS => [
                AddressInterface::STREET_NAME => $streetParts['street_name'],
                AddressInterface::STREET_NUMBER => $streetParts['street_number'],
                AddressInterface::ADDRESS_ADDITION => $streetParts['supplement'],
                AddressInterface::ZIP => $shippingAddress->getPostcode(),
                AddressInterface::CITY => $shippingAddress->getCity(),
                AddressInterface::COUNTRY => $countryDirectory->getName(),
                AddressInterface::COUNTRY_ISO_CODE => $countryDirectory->getData('iso2_code'),
                AddressInterface::STATE => $region,

            ],
        ];

        $this->info[ShippingInfoInterface::RECEIVER] = $receiver;

        // check if address includes postal facility data
        $station = $shippingAddress->getStreetFull();
        if (stripos($station, 'Packstation') === 0) {
            $packstationNumber = preg_filter('/^.*([\d]{3})($|\n.*)/', '$1', $station);
            $postNumber = is_numeric($shippingAddress->getCompany()) ? $shippingAddress->getCompany() : '';

            $this->setPackstation(
                $packstationNumber,
                $shippingAddress->getPostcode(),
                $shippingAddress->getCity(),
                $countryDirectory->getData('iso2_code'),
                $postNumber,
                $countryDirectory->getName(),
                $region
            );
        } elseif (stripos($station, 'Postfiliale') === 0) {
            $postfilialNumber = preg_filter('/^.*([\d]{3})($|\n.*)/', '$1', $station);
            $postNumber = is_numeric($shippingAddress->getCompany()) ? $shippingAddress->getCompany() : '';

            $this->setPostfiliale(
                $postfilialNumber,
                $postNumber,
                $shippingAddress->getPostcode(),
                $shippingAddress->getCity(),
                $countryDirectory->getData('iso2_code'),
                $countryDirectory->getName(),
                $region
            );
        }
    }

    /**
     * Update street.
     *
     * @param string $streetName
     * @param string $streetNumber
     * @param string $supplement
     */
    public function setStreet($streetName, $streetNumber, $supplement = '')
    {
        if (!isset($this->info[ShippingInfoInterface::RECEIVER])) {
            $this->info[ShippingInfoInterface::RECEIVER] = [];
        }

        if (!isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::ADDRESS])) {
            $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::ADDRESS] = [];
        }

        $receiverAddress = $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::ADDRESS];
        $receiverAddress[AddressInterface::STREET_NAME] = $streetName;
        $receiverAddress[AddressInterface::STREET_NUMBER] = $streetNumber;
        $receiverAddress[AddressInterface::ADDRESS_ADDITION] = $supplement;

        $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::ADDRESS] = $receiverAddress;
    }

    /**
     * Set service.
     *
     * @param string $code
     * @param bool $isActive
     * @param mixed $properties Key-value service configuration
     *
     * @return void
     */
    public function setService($code, $isActive, $properties = [])
    {
        if (!isset($this->info[ShippingInfoInterface::SERVICES])) {
            $this->info[ShippingInfoInterface::SERVICES] = [];
        }

        $service = [
            ServiceInterface::CODE => $code,
            ServiceInterface::IS_ACTIVE => $isActive,
            ServiceInterface::PROPERTIES => $properties,
        ];

        $this->info[ShippingInfoInterface::SERVICES][$code] = $service;
    }

    /**
     * Update Packstation data.
     *
     * @param string $station Packstation Number
     * @param string $zip Postal Code
     * @param string $city City
     * @param string $countryCode Country ISO Code
     * @param string $postNumber Consumer Post Number
     * @param string $country Country Name
     * @param string $state State/Region
     *
     * @return void
     */
    public function setPackstation($station, $zip, $city, $countryCode, $postNumber = '', $country = '', $state = '')
    {
        if (!isset($this->info[ShippingInfoInterface::RECEIVER])) {
            $this->info[ShippingInfoInterface::RECEIVER] = [];
        }

        $packstation = [
            PackstationInterface::PACKSTATION_NUMBER => $station,
            PackstationInterface::ZIP => $zip,
            PackstationInterface::CITY => $city,
            PackstationInterface::COUNTRY_ISO_CODE => $countryCode,
            PackstationInterface::POST_NUMBER => $postNumber,
            PackstationInterface::COUNTRY => $country,
            PackstationInterface::STATE => $state,
        ];

        $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PACKSTATION] = $packstation;
    }

    /**
     * @param string $station Packstation Number
     * @param string $postNumber Consumer Post Number
     * @param string $zip Postal Code
     * @param string $city City
     * @param string $countryCode Country ISO Code
     * @param string $country Country Name
     * @param string $state State/Region
     *
     * @return void
     */
    public function setPostfiliale($station, $postNumber, $zip, $city, $countryCode, $country = '', $state = '')
    {
        if (!isset($this->info[ShippingInfoInterface::RECEIVER])) {
            $this->info[ShippingInfoInterface::RECEIVER] = [];
        }

        $postfiliale = [
            PostfilialeInterface::POSTFILIAL_NUMBER => $station,
            PostfilialeInterface::ZIP => $zip,
            PostfilialeInterface::CITY => $city,
            PostfilialeInterface::COUNTRY_ISO_CODE => $countryCode,
            PostfilialeInterface::POST_NUMBER => $postNumber,
            PostfilialeInterface::COUNTRY => $country,
            PostfilialeInterface::STATE => $state,
        ];

        $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::POSTFILIALE] = $postfiliale;
    }

    /**
     * @param string $station Parcel Shop Number
     * @param string $zip Postal Code
     * @param string $city City
     * @param string $countryCode Country ISO Code
     * @param string $streetName Street Name
     * @param string $streetNumber Street Number
     * @param string $country Country Name
     * @param string $state State/Region
     *
     * @return void
     */
    public function setParcelShop(
        $station,
        $zip,
        $city,
        $countryCode,
        $streetName = '',
        $streetNumber = '',
        $country = '',
        $state = ''
    ) {
        if (!isset($this->info[ShippingInfoInterface::RECEIVER])) {
            $this->info[ShippingInfoInterface::RECEIVER] = [];
        }

        $parcelShop = [
            ParcelShopInterface::PARCEL_SHOP_NUMBER => $station,
            ParcelShopInterface::ZIP => $zip,
            ParcelShopInterface::CITY => $city,
            ParcelShopInterface::COUNTRY_ISO_CODE => $countryCode,
            ParcelShopInterface::STREET_NAME => $streetName,
            ParcelShopInterface::STREET_NUMBER => $streetNumber,
            ParcelShopInterface::COUNTRY => $country,
            ParcelShopInterface::STATE => $state,
        ];

        $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PARCEL_SHOP] = $parcelShop;
    }

    /**
     * @return void
     */
    public function unsetPackstation()
    {
        if (!isset($this->info[ShippingInfoInterface::RECEIVER])) {
            return;
        }

        if (isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PACKSTATION])) {
            unset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PACKSTATION]);
        }
    }

    /**
     * @return void
     */
    public function unsetPostfiliale()
    {
        if (!isset($this->info[ShippingInfoInterface::RECEIVER])) {
            return;
        }

        if (isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::POSTFILIALE])) {
            unset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::POSTFILIALE]);
        }
    }

    /**
     * @return void
     */
    public function unsetParcelShop()
    {
        if (!isset($this->info[ShippingInfoInterface::RECEIVER])) {
            return;
        }

        if (isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PARCEL_SHOP])) {
            unset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PARCEL_SHOP]);
        }
    }

    /**
     * @return ShippingInfoInterface
     */
    public function create()
    {
        if (!isset($this->info[ShippingInfoInterface::RECEIVER])) {
            return null;
        }

        // build parcel shop object
        $parcelShop = null;
        if (isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PARCEL_SHOP])) {
            $parcelShopData = $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PARCEL_SHOP];
            $parcelShop = $this->receiverParcelShopFactory->create($parcelShopData);
        }

        // build packstation object
        $packstation = null;
        if (isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PACKSTATION])) {
            $packstationData = $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::PACKSTATION];
            $packstation = $this->receiverPackstationFactory->create($packstationData);
        }

        // build postfiliale object
        $postfiliale = null;
        if (isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::POSTFILIALE])) {
            $postfilialeData = $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::POSTFILIALE];
            $postfiliale = $this->receiverPostfilialeFactory->create($postfilialeData);
        }

        // build address object
        $address = null;
        if (isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::ADDRESS])) {
            $addressData = $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::ADDRESS];
            $address = $this->receiverAddressFactory->create($addressData);
        }

        // build contact object
        $contact = null;
        if (isset($this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::ADDRESS])) {
            $contactData = $this->info[ShippingInfoInterface::RECEIVER][ReceiverInterface::CONTACT];
            $contact = $this->receiverContactFactory->create($contactData);
        }

        // build receiver object
        $receiverData = [
            ReceiverInterface::ADDRESS => $address,
            ReceiverInterface::CONTACT => $contact,
            ReceiverInterface::PACKSTATION => $packstation,
            ReceiverInterface::PARCEL_SHOP => $parcelShop,
            ReceiverInterface::POSTFILIALE => $postfiliale,
        ];
        $receiver = $this->receiverFactory->create($receiverData);

        // build service objects
        if (!isset($this->info[ShippingInfoInterface::SERVICES])) {
            $this->info[ShippingInfoInterface::SERVICES] = [];
        }

        $services = array_map(function ($serviceData) {
            $serviceProperties = [];
            foreach ($serviceData[ServiceInterface::PROPERTIES] as $key => $value) {
                $serviceProperty = $this->servicePropertyFactory->create([
                    ServicePropertyInterface::KEY => $key,
                    ServicePropertyInterface::VALUE => $value,
                ]);
                $serviceProperties[]= $serviceProperty;
            }

            $serviceData[ServiceInterface::PROPERTIES] = $serviceProperties;
            $service = $this->serviceFactory->create($serviceData);

            return $service;
        }, $this->info[ShippingInfoInterface::SERVICES]);

        $shippingInfo = $this->shippingInfoFactory->create([
            ShippingInfoInterface::SCHEMA_VERSION => self::SCHEMA_VERSION,
            ShippingInfoInterface::RECEIVER => $receiver,
            ShippingInfoInterface::SERVICES => $services,
        ]);

        // reset info array for next build
        $this->info = [];

        return $shippingInfo;
    }
}
