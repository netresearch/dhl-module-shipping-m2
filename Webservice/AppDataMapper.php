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
 * @package   Dhl\Versenden\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Webservice;

use \Dhl\Versenden\Api\Config\BcsConfigInterface;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ServiceInterface;
use \Dhl\Versenden\Api\Config\GlConfigInterface;
use \Dhl\Versenden\Api\Data\BcsProductProviderInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\Generic\Package\DimensionsInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\Generic\Package\MonetaryValueInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\Generic\Package\WeightInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\Contact\AddressInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\Contact\IdCardInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\CustomsDetailsInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\PackageInterface;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\PackageInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ReceiverInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ReturnReceiverInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ShipmentDetails\BankDataInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ShipmentDetailsInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ShipperInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrderInterfaceFactory;
use \Dhl\Versenden\Api\Webservice\Request\Mapper\AppDataMapperInterface;

/**
 * AppDataMapper
 *
 * @category Dhl
 * @package  Dhl\Versenden\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AppDataMapper implements AppDataMapperInterface
{
    /**
     * @var BcsConfigInterface
     */
    private $bcsConfig;

    /**
     * @var GlConfigInterface
     */
    private $glConfig;

    /**
     * @var BankDataInterfaceFactory
     */
    private $bankDataFactory;

    /**
     * @var ShipmentDetailsInterfaceFactory
     */
    private $shipmentDetailsFactory;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var IdCardInterfaceFactory
     */
    private $identityFactory;

    /**
     * @var ShipperInterfaceFactory
     */
    private $shipperFactory;

    /**
     * @var ReceiverInterfaceFactory
     */
    private $receiverFactory;

    /**
     * @var ReturnReceiverInterfaceFactory
     */
    private $returnReceiverFactory;

    /**
     * @var CustomsDetailsInterfaceFactory
     */
    private $customsDetailsFactory;

    /**
     * @var WeightInterfaceFactory
     */
    private $packageWeightFactory;

    /**
     * @var DimensionsInterfaceFactory
     */
    private $packageDimensionsFactory;

    /**
     * @var MonetaryValueInterfaceFactory
     */
    private $packageValueFactory;

    /**
     * @var PackageInterfaceFactory
     */
    private $packageFactory;

    /**
     * @var ShipmentOrderInterfaceFactory
     */
    private $shipmentOrderFactory;

    /**
     * @var BcsProductProviderInterfaceFactory
     */
    private $bcsProductProviderInterfaceFactory;

    /**
     * AppDataMapper constructor.
     * @param BcsConfigInterface $bcsConfig
     * @param GlConfigInterface $glConfig
     * @param BankDataInterfaceFactory $bankDataFactory
     * @param ShipmentDetailsInterfaceFactory $shipmentDetailsFactory
     * @param ShipmentOrderInterfaceFactory $shipmentOrderFactory
     * @param AddressInterfaceFactory $addressFactory
     * @param IdCardInterfaceFactory $identityFactory
     * @param ShipperInterfaceFactory $shipperFactory
     * @param ReceiverInterfaceFactory $receiverFactory
     * @param ReturnReceiverInterfaceFactory $returnReceiverFactory
     * @param CustomsDetailsInterfaceFactory $customsDetailsFactory
     * @param WeightInterfaceFactory $packageWeightFactory
     * @param DimensionsInterfaceFactory $packageDimensionsFactory,
     * @param MonetaryValueInterfaceFactory $packageValueFactory
     * @param PackageInterfaceFactory $packageFactory
     * @param BcsProductProviderInterfaceFactory $bcsProductProviderInterfaceFactory
     */
    public function __construct(
        BcsConfigInterface $bcsConfig,
        GlConfigInterface $glConfig,
        BankDataInterfaceFactory $bankDataFactory,
        ShipmentDetailsInterfaceFactory $shipmentDetailsFactory,
        ShipmentOrderInterfaceFactory $shipmentOrderFactory,
        AddressInterfaceFactory $addressFactory,
        IdCardInterfaceFactory $identityFactory,
        ShipperInterfaceFactory $shipperFactory,
        ReceiverInterfaceFactory $receiverFactory,
        ReturnReceiverInterfaceFactory $returnReceiverFactory,
        CustomsDetailsInterfaceFactory $customsDetailsFactory,
        WeightInterfaceFactory $packageWeightFactory,
        DimensionsInterfaceFactory $packageDimensionsFactory,
        MonetaryValueInterfaceFactory $packageValueFactory,
        PackageInterfaceFactory $packageFactory,
        BcsProductProviderInterfaceFactory $bcsProductProviderInterfaceFactory
    ) {
        $this->bcsConfig = $bcsConfig;
        $this->glConfig = $glConfig;
        $this->bankDataFactory = $bankDataFactory;
        $this->shipmentDetailsFactory = $shipmentDetailsFactory;
        $this->shipmentOrderFactory = $shipmentOrderFactory;
        $this->identityFactory = $identityFactory;
        $this->addressFactory = $addressFactory;
        $this->shipperFactory = $shipperFactory;
        $this->receiverFactory = $receiverFactory;
        $this->returnReceiverFactory = $returnReceiverFactory;
        $this->customsDetailsFactory = $customsDetailsFactory;
        $this->packageWeightFactory = $packageWeightFactory;
        $this->packageDimensionsFactory = $packageDimensionsFactory;
        $this->packageValueFactory = $packageValueFactory;
        $this->packageFactory = $packageFactory;
        $this->bcsProductProviderInterfaceFactory = $bcsProductProviderInterfaceFactory;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ShipmentDetailsInterface
     */
    private function getShipmentDetails(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId = $request->getOrderShipment()->getStoreId();

        $bankData = $this->bankDataFactory->create([
            'accountOwner' => $this->bcsConfig->getBankDataAccountOwner($storeId),
            'bankName' => $this->bcsConfig->getBankDataBankName($storeId),
            'iban' => $this->bcsConfig->getBankDataIban($storeId),
            'bic' => $this->bcsConfig->getBankDataBic($storeId),
            'notes' => $this->bcsConfig->getBankDataNote($storeId),
            'accountReference' => $this->bcsConfig->getBankDataAccountReference($storeId),
        ]);

        $shipmentDetails = $this->shipmentDetailsFactory->create([
            //TODO(nr): read from shipment request or config
            'isPrintOnlyIfCodeable' => $this->bcsConfig->isPrintOnlyIfCodeable($storeId), //TODO(nr): override with packaging settings
            'product' => 'V01PAK',
            'accountNumber' => '22222222220101',
            'returnShipmentAccountNumber' => '22222222220701',
            'pickupAccountNumber' => $this->glConfig->getPickupAccountNumber($storeId),
            'reference' => $request->getOrderShipment()->getIncrementId(),
            'returnShipmentReference' => $request->getOrderShipment()->getIncrementId(),
            'shipmentDate' => date("Y-m-d"),
            'bankData' => $bankData,
        ]);

        return $shipmentDetails;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ShipperInterface
     */
    private function getShipper(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId = $request->getOrderShipment()->getStoreId();

        //TODO(nr): split street
        $address = $this->addressFactory->create([
            'streetName' => $request->getShipperAddressStreet(),
            'postalCode' => $request->getShipperAddressPostalCode(),
            'city' => $request->getShipperAddressCity(),
            'state' => $request->getShipperAddressStateOrProvinceCode(),
            'countryCode' => $request->getShipperAddressCountryCode(),
            'dispatchingInformation' => $this->bcsConfig->getDispatchingInformation($storeId)
        ]);

        $shipper = $this->shipperFactory->create([
            'contactPerson' => $this->bcsConfig->getContactPerson($storeId),
            'name' => [
                $request->getShipperContactPersonName(),
                $request->getShipperContactCompanyName(),
                $this->bcsConfig->getShipperCompanyAddition($storeId),
            ],
            'companyName' => $request->getShipperContactCompanyName(),
            'phone' => $request->getShipperContactPhoneNumber(),
            'email' => $request->getData('shipper_email'),
            'address' => $address,
        ]);

        return $shipper;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ReceiverInterface
     */
    private function getReceiver(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId = $request->getOrderShipment()->getStoreId();

        $address = $this->addressFactory->create([
            'streetName' => $request->getRecipientAddressStreet(),
            'postalCode' => $request->getRecipientAddressPostalCode(),
            'city' => $request->getRecipientAddressCity(),
            'state' => $request->getRecipientAddressStateOrProvinceCode(),
            'countryCode' => $request->getRecipientAddressCountryCode(),
            'dispatchingInformation' => $this->bcsConfig->getDispatchingInformation($storeId)
        ]);

        $receiver = $this->receiverFactory->create([
            'contactPerson' => $request->getRecipientContactPersonName(),
            'name' => [
                $request->getRecipientContactPersonName(),
                $request->getRecipientContactCompanyName(),
            ],
            'companyName' => $request->getRecipientContactCompanyName(),
            'phone' => $request->getRecipientContactPhoneNumber(),
            'email' => $request->getData('recipient_email'),
            'address' => $address,
        ]);

        return $receiver;
    }

    /**
     * TODO(nr): allow other return receiver than shipping origin.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ReturnReceiverInterface
     */
    private function getReturnReceiver(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId = $request->getOrderShipment()->getStoreId();

        $address = $this->addressFactory->create([
            'streetName' => $request->getShipperAddressStreet(),
            'postalCode' => $request->getShipperAddressPostalCode(),
            'city' => $request->getShipperAddressCity(),
            'state' => $request->getShipperAddressStateOrProvinceCode(),
            'countryCode' => $request->getShipperAddressCountryCode(),
            'dispatchingInformation' => $this->bcsConfig->getDispatchingInformation($storeId)
        ]);

        $returnReceiver = $this->returnReceiverFactory->create([
            'contactPerson' => $this->bcsConfig->getContactPerson($storeId),
            'name' => [
                $request->getShipperContactPersonName(),
                $request->getShipperContactCompanyName(),
                $this->bcsConfig->getShipperCompanyAddition($storeId),
            ],
            'companyName' => $request->getShipperContactCompanyName(),
            'phone' => $request->getShipperContactPhoneNumber(),
            'email' => $request->getData('shipper_email'),
            'address' => $address,
        ]);

        return $returnReceiver;
    }

    /**
     * TODO(nr): allow international shipping
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\CustomsDetailsInterface
     */
    private function getCustomsDetails(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $customsDetails = $this->customsDetailsFactory->create([
            'invoiceNumber' => '???',
            'exportType' => '',
            'termsOfTrade' => '',
            'placeOfCommital' => '',
            'additionalFee' => '',
            'permitNumber' => '',
            'attestationNumber' => '',
            'isWithElectronicExportNtfctn' => false,
            'positions' => [],
        ]);

        return $customsDetails;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return PackageInterface[]
     */
    private function getPackages(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $packages = [];
        foreach ($request->getData('packages') as $packageId => $package) {
            $weight = $this->packageWeightFactory->create([
                //TODO(nr): fix keys
                'value' => $package['weight'],
                'unitOfMeasurement' => $package['weight_unit'],
            ]);
            $dimensions = $this->packageDimensionsFactory->create([
                //TODO(nr): fix keys
                'length' => $package['length'],
                'width' => $package['width'],
                'height' => $package['height'],
                'unitOfMeasurement' => $package['dimensions_unit'],
            ]);
            $declaredValue = $this->packageValueFactory->create([
                //TODO(nr): fix keys
                'value' => $package['value'],
                'currencyCode' => $package['currency_code']
            ]);

            $packages[]= $this->packageFactory->create([
                'packageId' => $packageId,
                'weight' => $weight,
                'dimensions' => $dimensions,
                'declaredValue' => $declaredValue,
            ]);
        }

        return $packages;
    }

    /**
     * @return ServiceInterface[]
     */
    private function getServices(\Magento\Shipping\Model\Shipment\Request $request)
    {
        return [];
    }

    /**
     * Convert M2 shipment request to platform independent request object.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @param string $sequenceNumber
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrderInterface
     */
    public function mapShipmentRequest($request, $sequenceNumber)
    {
        $shipmentDetails = $this->getShipmentDetails($request);
        $shipper = $this->getShipper($request);
        $receiver = $this->getReceiver($request);
        $returnReceiver = $this->getReturnReceiver($request);
        $customsDetails = $this->getCustomsDetails($request);
        $packages = $this->getPackages($request);
        $services = $this->getServices($request);

        $shipmentOrder = $this->shipmentOrderFactory->create([
            'sequenceNumber' => $sequenceNumber,
            'shipmentDetails' => $shipmentDetails,
            'shipper' => $shipper,
            'receiver' => $receiver,
            'returnReceiver' => $returnReceiver,
            'customsDetails' => $customsDetails,
            'packages' => $packages,
            'services' => $services,
        ]);

        return $shipmentOrder;
    }
}
