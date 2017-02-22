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
use \Dhl\Versenden\Api\Config\ModuleConfigInterface;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\CustomsDetails\ExportTypeInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ServiceInterface;
use \Dhl\Versenden\Api\Config\GlConfigInterface;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\Generic\Package\DimensionsInterfaceFactory;
use Dhl\Versenden\Api\Data\Webservice\Request\Type\Generic\Package\MonetaryValueInterface;
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
use \Dhl\Versenden\Api\ShippingInfoRepositoryInterface;
use \Dhl\Versenden\Webservice\Request\Type\CreateShipment\ShipmentOrder\Service\ServiceFactory;
use \Dhl\Versenden\Api\StreetSplitterInterface;
use \Dhl\Versenden\Api\Webservice\BcsAccessDataInterface;
use \Dhl\Versenden\Api\Webservice\Request\Mapper\AppDataMapperInterface;
use \Dhl\Versenden\Webservice\ShippingInfo\Info;
use \Magento\Framework\Exception\NoSuchEntityException;

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
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var BcsAccessDataInterface
     */
    private $bcsAccessData;

    /**
     * @var StreetSplitterInterface
     */
    private $streetSplitter;

    /**
     * @var ShippingInfoRepositoryInterface
     */
    private $orderInfoRepository;

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
     * @var ExportTypeInterfaceFactory
     */
    private $exportTypeInterfaceFactory;

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * AppDataMapper constructor.
     *
     * @param BcsConfigInterface                $bcsConfig
     * @param GlConfigInterface                 $glConfig
     * @param ModuleConfigInterface             $moduleConfig
     * @param BcsAccessDataInterface            $bcsAccessData
     * @param StreetSplitterInterface           $streetSplitter
     * @param ShippingInfoRepositoryInterface   $orderInfoRepository
     * @param BankDataInterfaceFactory          $bankDataFactory
     * @param ShipmentDetailsInterfaceFactory   $shipmentDetailsFactory
     * @param ShipmentOrderInterfaceFactory     $shipmentOrderFactory
     * @param AddressInterfaceFactory           $addressFactory
     * @param IdCardInterfaceFactory            $identityFactory
     * @param ShipperInterfaceFactory           $shipperFactory
     * @param ReceiverInterfaceFactory          $receiverFactory
     * @param ReturnReceiverInterfaceFactory    $returnReceiverFactory
     * @param CustomsDetailsInterfaceFactory    $customsDetailsFactory
     * @param WeightInterfaceFactory            $packageWeightFactory
     * @param DimensionsInterfaceFactory        $packageDimensionsFactory
     * @param MonetaryValueInterfaceFactory     $packageValueFactory
     * @param PackageInterfaceFactory           $packageFactory
     * @param ExportTypeInterfaceFactory        $exportTypeInterfaceFactory
     * @param ServiceFactory                    $serviceFactory
     */
    public function __construct(
        BcsConfigInterface $bcsConfig,
        GlConfigInterface $glConfig,
        ModuleConfigInterface $moduleConfig,
        BcsAccessDataInterface $bcsAccessData,
        StreetSplitterInterface $streetSplitter,
        ShippingInfoRepositoryInterface $orderInfoRepository,
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
        ExportTypeInterfaceFactory $exportTypeInterfaceFactory,
        ServiceFactory $serviceFactory
    ) {
        $this->bcsConfig                    = $bcsConfig;
        $this->glConfig                     = $glConfig;
        $this->moduleConfig                 = $moduleConfig;
        $this->bcsAccessData                = $bcsAccessData;
        $this->streetSplitter               = $streetSplitter;
        $this->orderInfoRepository          = $orderInfoRepository;
        $this->bankDataFactory              = $bankDataFactory;
        $this->shipmentDetailsFactory       = $shipmentDetailsFactory;
        $this->shipmentOrderFactory         = $shipmentOrderFactory;
        $this->identityFactory              = $identityFactory;
        $this->addressFactory               = $addressFactory;
        $this->shipperFactory               = $shipperFactory;
        $this->receiverFactory              = $receiverFactory;
        $this->returnReceiverFactory        = $returnReceiverFactory;
        $this->customsDetailsFactory        = $customsDetailsFactory;
        $this->packageWeightFactory         = $packageWeightFactory;
        $this->packageDimensionsFactory     = $packageDimensionsFactory;
        $this->packageValueFactory          = $packageValueFactory;
        $this->packageFactory               = $packageFactory;
        $this->exportTypeInterfaceFactory   = $exportTypeInterfaceFactory;
        $this->serviceFactory               = $serviceFactory;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return MonetaryValueInterface
     */
    private function getShipmentValue(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $shipmentValue = 0;
        foreach ($request->getData('packages') as $packageId => $package) {
            $shipmentValue += array_reduce($package['items'], function ($carry, $item) {
                // precision: 3
                $price = $item['price'] * 1000;
                $carry += ($price * $item['qty']);

                return $carry;
            }, $shipmentValue);
        }

        $declaredValue = $this->packageValueFactory->create([
            'value' => 0.001 * $shipmentValue,
            'currencyCode' => $request->getData('base_currency_code'),
        ]);

        return $declaredValue;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ShipmentDetailsInterface
     */
    private function getShipmentDetails(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId = $request->getOrderShipment()->getStoreId();

        $bankData = $this->bankDataFactory->create([
            'accountOwner'     => $this->bcsConfig->getBankDataAccountOwner($storeId),
            'bankName'         => $this->bcsConfig->getBankDataBankName($storeId),
            'iban'             => $this->bcsConfig->getBankDataIban($storeId),
            'bic'              => $this->bcsConfig->getBankDataBic($storeId),
            'notes'            => $this->bcsConfig->getBankDataNote($storeId),
            'accountReference' => $this->bcsConfig->getBankDataAccountReference($storeId),
        ]);

        $product = $this->bcsAccessData->getProductCode(
            $request->getShipperAddressCountryCode(),
            $request->getRecipientAddressCountryCode(),
            $this->moduleConfig->getEuCountryList()
        );

        $shipmentDetails = $this->shipmentDetailsFactory->create([
            'isPrintOnlyIfCodeable'       => $this->bcsConfig->isPrintOnlyIfCodeable($storeId),
            //TODO(nr): override with packaging settings
            'product'                     => $product,
            'accountNumber'               => $this->bcsAccessData->getBillingNumber($product),
            'returnShipmentAccountNumber' => $this->bcsAccessData->getReturnShipmentBillingNumber($product),
            'pickupAccountNumber'         => $this->glConfig->getPickupAccountNumber($storeId),
            'reference'                   => $request->getOrderShipment()->getOrder()->getIncrementId(),
            'returnShipmentReference'     => $request->getOrderShipment()->getOrder()->getIncrementId(),
            'shipmentDate'                => date("Y-m-d"),
            'bankData'                    => $bankData,
        ]);

        return $shipmentDetails;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ShipperInterface
     */
    private function getShipper(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId      = $request->getOrderShipment()->getStoreId();
        $addressParts = $this->streetSplitter->splitStreet($request->getShipperAddressStreet());

        $address = $this->addressFactory->create([
            'street'                 => $request->getShipperAddressStreet(),
            'streetName'             => $addressParts['street_name'],
            'streetNumber'           => $addressParts['street_number'],
            'addressAddition'        => $addressParts['supplement'],
            'postalCode'             => $request->getShipperAddressPostalCode(),
            'city'                   => $request->getShipperAddressCity(),
            'state'                  => $request->getShipperAddressStateOrProvinceCode(),
            'countryCode'            => $request->getShipperAddressCountryCode(),
            'dispatchingInformation' => $this->bcsConfig->getDispatchingInformation($storeId)
        ]);

        $shipper = $this->shipperFactory->create([
            'contactPerson' => $this->bcsConfig->getContactPerson($storeId),
            'name'          => [
                $request->getShipperContactPersonName(),
                $request->getShipperContactCompanyName(),
                $this->bcsConfig->getShipperCompanyAddition($storeId),
            ],
            'companyName'   => $request->getShipperContactCompanyName(),
            'phone'         => $request->getShipperContactPhoneNumber(),
            'email'         => $request->getData('shipper_email'),
            'address'       => $address,
        ]);

        return $shipper;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ReceiverInterface
     * @throws NoSuchEntityException
     */
    private function getReceiver(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId = $request->getOrderShipment()->getStoreId();

        try {
            $shippingInfoEntry = $this->orderInfoRepository->getById(
                $request->getOrderShipment()->getOrder()->getShippingAddress()->getEntityId()
            );
            /** @var Info $shippingInfo */
            $shippingInfo = Info::fromJson($shippingInfoEntry->getInfo());
        } catch (NoSuchEntityException $e) {
            $shippingInfo = '';
        }

        if (!empty($shippingInfo)) {
            $addressParts = [
                'street_name'   => $shippingInfo->getReceiver()->streetName,
                'street_number' => $shippingInfo->getReceiver()->streetNumber,
                'supplement'    => $shippingInfo->getReceiver()->addressAddition,
            ];
        } else {
            $addressParts = $this->streetSplitter->splitStreet($request->getRecipientAddressStreet());
        }

        $address = $this->addressFactory->create(
            [
                'street'                 => $request->getRecipientAddressStreet(),
                'streetName'             => $addressParts['street_name'],
                'streetNumber'           => $addressParts['street_number'],
                'addressAddition'        => $addressParts['supplement'],
                'postalCode'             => $request->getRecipientAddressPostalCode(),
                'city'                   => $request->getRecipientAddressCity(),
                'state'                  => $request->getRecipientAddressStateOrProvinceCode(),
                'countryCode'            => $request->getRecipientAddressCountryCode(),
                'dispatchingInformation' => $this->bcsConfig->getDispatchingInformation($storeId)
            ]
        );

        $receiver = $this->receiverFactory->create(
            [
                'contactPerson' => $request->getRecipientContactPersonName(),
                'name'          => [
                    $request->getRecipientContactPersonName(),
                    $request->getRecipientContactCompanyName(),
                ],
                'companyName'   => $request->getRecipientContactCompanyName(),
                'phone'         => $request->getRecipientContactPhoneNumber(),
                'email'         => $request->getData('recipient_email'),
                'address'       => $address,
            ]
        );

        return $receiver;
    }

    /**
     * TODO(nr): allow other return receiver than shipping origin.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ReturnReceiverInterface
     */
    private function getReturnReceiver(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId      = $request->getOrderShipment()->getStoreId();
        $addressParts = $this->streetSplitter->splitStreet($request->getShipperAddressStreet());

        $address = $this->addressFactory->create([
            'street'                 => $request->getRecipientAddressStreet(),
            'streetName'             => $addressParts['street_name'],
            'streetNumber'           => $addressParts['street_number'],
            'addressAddition'        => $addressParts['supplement'],
            'postalCode'             => $request->getShipperAddressPostalCode(),
            'city'                   => $request->getShipperAddressCity(),
            'state'                  => $request->getShipperAddressStateOrProvinceCode(),
            'countryCode'            => $request->getShipperAddressCountryCode(),
            'dispatchingInformation' => $this->bcsConfig->getDispatchingInformation($storeId)
        ]);

        $returnReceiver = $this->returnReceiverFactory->create([
            'contactPerson' => $this->bcsConfig->getContactPerson($storeId),
            'name'          => [
                $request->getShipperContactPersonName(),
                $request->getShipperContactCompanyName(),
                $this->bcsConfig->getShipperCompanyAddition($storeId),
            ],
            'companyName'   => $request->getShipperContactCompanyName(),
            'phone'         => $request->getShipperContactPhoneNumber(),
            'email'         => $request->getData('shipper_email'),
            'address'       => $address,
        ]);

        return $returnReceiver;
    }

    /**
     * TODO(nr): allow international shipping
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\CustomsDetailsInterface
     */
    private function getCustomsDetails(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $exportType = $this->exportTypeInterfaceFactory->create();

        $customsDetails = $this->customsDetailsFactory->create([
            'invoiceNumber'                => '???',
            'exportType'                   => $exportType,
            'termsOfTrade'                 => '',
            'placeOfCommital'              => '',
            'additionalFee'                => '',
            'permitNumber'                 => '',
            'attestationNumber'            => '',
            'isWithElectronicExportNtfctn' => false,
            'positions'                    => [],
        ]);

        return $customsDetails;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return PackageInterface[]
     */
    private function getPackages(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $packages = [];

        foreach ($request->getData('packages') as $packageId => $package) {
            $weight = $this->packageWeightFactory->create([
                'value' => $package['params']['weight'],
                'unitOfMeasurement' => $package['params']['weight_units'],
            ]);
            $dimensions = $this->packageDimensionsFactory->create([
                'length' => $package['params']['length'],
                'width' => $package['params']['width'],
                'height' => $package['params']['height'],
                'unitOfMeasurement' => $package['params']['dimension_units'],
            ]);

            $packageValue = array_reduce($package['items'], function ($carry, $item) {
                $price = $item['price'] * 1000;
                $carry += ($price * $item['qty']);

                return $carry;
            });
            $packageValue = number_format($packageValue / 1000, 2, '.', '');

            $declaredValue = $this->packageValueFactory->create([
                'value' => $packageValue,
                'currencyCode' => $request->getData('base_currency_code'),
            ]);

            $packages[] = $this->packageFactory->create([
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
        $services = [];

        $paymentMethod = $request->getOrderShipment()->getOrder()->getPayment()->getMethod();
        if ($this->moduleConfig->isCodPaymentMethod($paymentMethod)) {
            $services['cod'] = $this->serviceFactory->create('cod', [
                'codAmount' => $this->getShipmentValue($request),
                'addFee' => true,
            ]);
        }

        return $services;
    }

    /**
     * Convert M2 shipment request to platform independent request object.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @param string                                   $sequenceNumber
     *
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrderInterface
     */
    public function mapShipmentRequest($request, $sequenceNumber)
    {
        $shipmentDetails = $this->getShipmentDetails($request);
        $shipper         = $this->getShipper($request);
        $receiver        = $this->getReceiver($request);
        $returnReceiver  = $this->getReturnReceiver($request);
        $customsDetails  = $this->getCustomsDetails($request);
        $packages        = $this->getPackages($request);
        $services        = $this->getServices($request);

        $shipmentOrder = $this->shipmentOrderFactory->create([
            'sequenceNumber'  => $sequenceNumber,
            'shipmentDetails' => $shipmentDetails,
            'shipper'         => $shipper,
            'receiver'        => $receiver,
            'returnReceiver'  => $returnReceiver,
            'customsDetails'  => $customsDetails,
            'packages'        => $packages,
            'services'        => $services,
        ]);

        return $shipmentOrder;
    }
}
