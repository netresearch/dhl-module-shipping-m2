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
 * @package   Dhl\Shipping\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Webservice;

use \Dhl\Shipping\Config\BcsConfigInterface;
use \Dhl\Shipping\Model\Config\ModuleConfigInterface;
use \Dhl\Shipping\Config\GlConfigInterface;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrderInterface;
use \Dhl\Shipping\Webservice\RequestType\Generic\Package\DimensionsInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\Generic\Package\MonetaryValueInterface;
use \Dhl\Shipping\Webservice\RequestType\Generic\Package\MonetaryValueInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\Generic\Package\WeightInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\AddressInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\IdCardInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\ReceiverInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\ReturnReceiverInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\ShipperInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\CustomsDetails;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\PackageInterface;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\PackageInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\ShipmentDetails\BankDataInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\ShipmentDetails\ShipmentDetailsInterface;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\ShipmentDetails\ShipmentDetailsInterfaceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrderInterfaceFactory;
use \Dhl\Shipping\Model\ShippingInfo\ShippingInfoRepositoryInterface;
use Dhl\Shipping\Util\BcsShippingProductsInterface;
use Dhl\Shipping\Util\GlShippingProductsInterface;
use Dhl\Shipping\Util\ShippingProductsInterface;
use \Dhl\Shipping\Webservice\Exception\CreateShipmentValidationException;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\CustomsDetails\ExportPositionFactory;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Package\PackageItem;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\AbstractServiceFactory;
use \Dhl\Shipping\Util\StreetSplitterInterface;
use \Dhl\Shipping\Webservice\RequestMapper\AppDataMapperInterface;
use Dhl\Shipping\Webservice\ShippingInfo\Info;

/**
 * AppDataMapper
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
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
     * @var ShippingProductsInterface|BcsShippingProductsInterface|GlShippingProductsInterface
     */
    private $shippingProducts;

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
     * @var CustomsDetails\CustomsDetailsInterfaceFactory
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
    private $monetaryValueFactory;

    /**
     * @var PackageInterfaceFactory
     */
    private $packageFactory;

    /**
     * @var PackageItemInterfaceFactory
     */
    private $packageItemFactory;

    /**
     * @var Service\ServiceCollectionInterface
     */
    private $serviceCollection;

    /**
     * @var CustomsDetails\ExportTypeInterfaceFactory
     */
    private $exportTypeInterfaceFactory;

    /**
     * @var ShipmentOrderInterfaceFactory
     */
    private $shipmentOrderFactory;

    /**
     * @var RequestValidatorInterface
     */
    private $requestValidator;

    /** @var  ExportPositionFactory */
    private $exportPositionFactory;

    /**
     * AppDataMapper constructor.
     *
     * @param BcsConfigInterface $bcsConfig
     * @param GlConfigInterface $glConfig
     * @param ModuleConfigInterface $moduleConfig
     * @param ShippingProductsInterface $shippingProducts
     * @param StreetSplitterInterface $streetSplitter
     * @param ShippingInfoRepositoryInterface $orderInfoRepository
     * @param BankDataInterfaceFactory $bankDataFactory
     * @param ShipmentDetailsInterfaceFactory $shipmentDetailsFactory
     * @param AddressInterfaceFactory $addressFactory
     * @param IdCardInterfaceFactory $identityFactory
     * @param ShipperInterfaceFactory $shipperFactory
     * @param ReceiverInterfaceFactory $receiverFactory
     * @param ReturnReceiverInterfaceFactory $returnReceiverFactory
     * @param CustomsDetails\CustomsDetailsInterfaceFactory $customsDetailsFactory
     * @param CustomsDetails\ExportTypeInterfaceFactory $exportTypeInterfaceFactory
     * @param WeightInterfaceFactory $packageWeightFactory
     * @param DimensionsInterfaceFactory $packageDimensionsFactory ,
     * @param MonetaryValueInterfaceFactory $packageValueFactory
     * @param PackageInterfaceFactory $packageFactory
     * @param Service\ServiceCollectionInterface $serviceCollection
     * @param ShipmentOrderInterfaceFactory $shipmentOrderFactory
     * @param RequestValidatorInterface $requestValidator
     */
    public function __construct(
        BcsConfigInterface $bcsConfig,
        GlConfigInterface $glConfig,
        ModuleConfigInterface $moduleConfig,
        ShippingProductsInterface $shippingProducts,
        StreetSplitterInterface $streetSplitter,
        ShippingInfoRepositoryInterface $orderInfoRepository,
        BankDataInterfaceFactory $bankDataFactory,
        ShipmentDetailsInterfaceFactory $shipmentDetailsFactory,
        AddressInterfaceFactory $addressFactory,
        IdCardInterfaceFactory $identityFactory,
        ShipperInterfaceFactory $shipperFactory,
        ReceiverInterfaceFactory $receiverFactory,
        ReturnReceiverInterfaceFactory $returnReceiverFactory,
        CustomsDetails\CustomsDetailsInterfaceFactory $customsDetailsFactory,
        CustomsDetails\ExportTypeInterfaceFactory $exportTypeInterfaceFactory,
        WeightInterfaceFactory $packageWeightFactory,
        DimensionsInterfaceFactory $packageDimensionsFactory,
        MonetaryValueInterfaceFactory $packageValueFactory,
        PackageInterfaceFactory $packageFactory,
        Service\ServiceCollectionInterface $serviceCollection,
        ShipmentOrderInterfaceFactory $shipmentOrderFactory,
        RequestValidatorInterface $requestValidator,
        ExportPositionFactory $exportPositionFactory,
        PackageItemInterfaceFactory $packageItemInterfaceFactory
    ) {
        $this->bcsConfig                    = $bcsConfig;
        $this->glConfig                     = $glConfig;
        $this->moduleConfig                 = $moduleConfig;
        $this->shippingProducts             = $shippingProducts;
        $this->streetSplitter               = $streetSplitter;
        $this->orderInfoRepository          = $orderInfoRepository;
        $this->bankDataFactory              = $bankDataFactory;
        $this->shipmentDetailsFactory       = $shipmentDetailsFactory;
        $this->identityFactory              = $identityFactory;
        $this->addressFactory               = $addressFactory;
        $this->shipperFactory               = $shipperFactory;
        $this->receiverFactory              = $receiverFactory;
        $this->returnReceiverFactory        = $returnReceiverFactory;
        $this->customsDetailsFactory        = $customsDetailsFactory;
        $this->exportTypeInterfaceFactory   = $exportTypeInterfaceFactory;
        $this->packageWeightFactory         = $packageWeightFactory;
        $this->packageDimensionsFactory     = $packageDimensionsFactory;
        $this->monetaryValueFactory         = $packageValueFactory;
        $this->packageFactory               = $packageFactory;
        $this->serviceCollection            = $serviceCollection;
        $this->shipmentOrderFactory         = $shipmentOrderFactory;
        $this->requestValidator             = $requestValidator;
        $this->exportPositionFactory        = $exportPositionFactory;
        $this->packageItemFactory           = $packageItemInterfaceFactory;
    }

    /**
     * Calculate total value of order
     * FIXME(nr): handle partial shipments
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return MonetaryValueInterface
     */
    public function getOrderValue(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $shipmentValue = $request->getOrderShipment()->getOrder()->getBaseGrandTotal();
        $declaredValue = $this->monetaryValueFactory->create([
            'value' => $shipmentValue,
            'currencyCode' => $request->getData('base_currency_code'),
        ]);

        return $declaredValue;
    }

    /**
     * Calculate total value of (partial) shipment
     * FIXME(nr): obtain value including tax
     *
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

        $declaredValue = $this->monetaryValueFactory->create([
            'value' => 0.001 * $shipmentValue,
            'currencyCode' => $request->getData('base_currency_code'),
        ]);

        return $declaredValue;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return ShipmentDetailsInterface
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

        $qtyOrdered = $request->getOrderShipment()->getOrder()->getTotalQtyOrdered();
        $qtyShipped = $request->getOrderShipment()->getTotalQty();
        $productCode = $request->getData('packaging_type');
        $shipmentComment = $request->getOrderShipment()->getCustomerNote();

        $ekp = $this->bcsConfig->getAccountEkp($storeId);
        $participations = $this->bcsConfig->getAccountParticipations($storeId);

        $billingNumber = $this->shippingProducts->getBillingNumber($productCode, $ekp, $participations);
        $returnBillingNumber = $this->shippingProducts->getReturnBillingNumber($productCode, $ekp, $participations);

        $shipmentDetails = $this->shipmentDetailsFactory->create([
            'isPrintOnlyIfCodeable'       => $this->bcsConfig->isPrintOnlyIfCodeable($storeId),
            'isPartialShipment'           => ($qtyOrdered != $qtyShipped) || (count($request->getData('packages')) > 1),
            'product'                     => $productCode,
            'accountNumber'               => $billingNumber,
            'returnShipmentAccountNumber' => $returnBillingNumber,
            'pickupAccountNumber'         => $this->glConfig->getPickupAccountNumber($storeId),
            'distributionCenter'          => $this->glConfig->getDistributionCenter($storeId),
            'customerPrefix'              => $this->glConfig->getCustomerPrefix($storeId),
            'reference'                   => $request->getOrderShipment()->getOrder()->getIncrementId(),
            'returnShipmentReference'     => $request->getOrderShipment()->getOrder()->getIncrementId(),
            'shipmentDate'                => date("Y-m-d"),
            'bankData'                    => $bankData,
            'shipmentComment'             => $shipmentComment,
        ]);

        return $shipmentDetails;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\ShipperInterface
     */
    private function getShipper(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId      = $request->getOrderShipment()->getStoreId();
        $addressParts = $this->streetSplitter->splitStreet($request->getShipperAddressStreet());

        $address = $this->addressFactory->create([
            'street'                 => [$request->getShipperAddressStreet1(), $request->getShipperAddressStreet2()],
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
            'companyName'   => $request->getShipperContactCompanyName(),
            'name'          => null,
            'nameAddition'  => $this->bcsConfig->getShipperCompanyAddition($storeId),
            'contactPerson' => $request->getShipperContactPersonName(),
            'phone'         => $request->getShipperContactPhoneNumber(),
            'email'         => $request->getData('shipper_email'),
            'address'       => $address,
        ]);

        return $shipper;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\ReceiverInterface
     */
    private function getReceiver(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId = $request->getOrderShipment()->getStoreId();

        $addressId = $request->getOrderShipment()->getOrder()->getShippingAddress()->getEntityId();
        /** @var Info $shippingInfo */
        $shippingInfo = $this->orderInfoRepository->getInfoData($addressId);
        if (!$shippingInfo) {
            $addressParts = $this->streetSplitter->splitStreet($request->getRecipientAddressStreet());
        } else {
            $addressParts = [
                'street_name' => $shippingInfo->getReceiver()->streetName,
                'street_number' => $shippingInfo->getReceiver()->streetNumber,
                'supplement' => $shippingInfo->getReceiver()->addressAddition,
            ];
        }

        $address = $this->addressFactory->create([
            'street'                 => [$request->getRecipientAddressStreet1(), $request->getRecipientAddressStreet2()],
            'streetName'             => $addressParts['street_name'],
            'streetNumber'           => $addressParts['street_number'],
            'addressAddition'        => $addressParts['supplement'],
            'postalCode'             => $request->getRecipientAddressPostalCode(),
            'city'                   => $request->getRecipientAddressCity(),
            'state'                  => $request->getRecipientAddressStateOrProvinceCode(),
            'countryCode'            => $request->getRecipientAddressCountryCode(),
            'dispatchingInformation' => $this->bcsConfig->getDispatchingInformation($storeId)
        ]);

        $idCard = $this->identityFactory->create([
            'type' => '',
            'number' => '',
        ]);

        $receiver = $this->receiverFactory->create([
            'companyName'   => $request->getRecipientContactCompanyName(),
            'name'          => $request->getRecipientContactPersonName(),
            'nameAddition'  => null,
            'contactPerson' => $request->getRecipientContactPersonName(),
            'phone'         => $request->getRecipientContactPhoneNumber(),
            'email'         => $request->getData('recipient_email'),
            'address'       => $address,
            'identity'      => $idCard,
        ]);

        return $receiver;
    }

    /**
     * TODO(nr): allow other return receiver than shipping origin.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\ReturnReceiverInterface
     */
    private function getReturnReceiver(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId      = $request->getOrderShipment()->getStoreId();
        $addressParts = $this->streetSplitter->splitStreet($request->getShipperAddressStreet());

        $address = $this->addressFactory->create([
            'street'                 => [$request->getShipperAddressStreet1(), $request->getShipperAddressStreet2()],
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
            'companyName'   => $request->getShipperContactCompanyName(),
            'name'          => null,
            'nameAddition'  => $this->bcsConfig->getShipperCompanyAddition($storeId),
            'contactPerson' => $request->getShipperContactPersonName(),
            'phone'         => $request->getShipperContactPhoneNumber(),
            'email'         => $request->getData('shipper_email'),
            'address'       => $address,
        ]);

        return $returnReceiver;
    }

    /**
     * @return Service\ServiceCollectionInterface
     */
    private function getServices(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $paymentMethod = $request->getOrderShipment()->getOrder()->getPayment()->getMethod();
        if ($this->moduleConfig->isCodPaymentMethod($paymentMethod)) {
            $this->serviceCollection->addService(AbstractServiceFactory::SERVICE_CODE_COD, [
                'codAmount' => $this->getOrderValue($request),
                'addFee' => true,
            ]);
        }

        return $this->serviceCollection;
    }


    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     *
     * @return PackageInterface
     */
    private function getPackage(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $allPackages = $request->getData('packages');
        $packageId   = $request->getData('package_id');
        $package     = $allPackages[$packageId];

        $packageItems = [];
        foreach ($package['items'] as $item) {
            /** @var PackageItem $packageItem */
            $packageItem = $this->packageItemFactory->create([
                'qty' => isset($item['qty']) ? $item['qty'] : null,
                'customsValue' => isset($item['customs_value']) ? $item['customs_value'] : null,
                'customsItemDescription' => isset($item['customs_item_description']) ? $item['customs_item_description'] : null,
                'price' => isset($item['price']) ? $item['price'] : null,
                'name' => isset($item['name']) ? $item['name'] : null,
                'weight' => isset($item['weight']) ?  $item['weight'] : null,
                'productId' => isset($item['product_id']) ? $item['product_id'] : null,
                'orderItemId' => isset($item['order_item_id']) ? $item['order_item_id'] :  null,
                'tariffNumber' => isset($item['tariff_number']) ? $item['tariff_number'] :  null,
                'itemOriginCountry' => isset($item['item_origin_country']) ? $item['item_origin_country'] : null,
            ]);
            $packageItems[]= $packageItem;
        }

        $weight = $this->packageWeightFactory->create([
            'value'             => $package['params']['weight'],
            'unitOfMeasurement' => $package['params']['weight_units'],
        ]);
        $dimensions = $this->packageDimensionsFactory->create([
            'length'            => $package['params']['length'],
            'width'             => $package['params']['width'],
            'height'            => $package['params']['height'],
            'unitOfMeasurement' => $package['params']['dimension_units'],
        ]);

        $packageValue = array_reduce($package['items'], function ($carry, $item) {
            $price  = $item['price'] * 1000;
            $carry += ($price * $item['qty']);

            return $carry;
        });
        $packageValue = number_format($packageValue / 1000, 2, '.', '');

        $declaredValue = $this->monetaryValueFactory->create([
            'value'        => $packageValue,
            'currencyCode' => $request->getData('base_currency_code'),
        ]);

        $packageParams = isset($package['params']) ? $package['params'] : [];
        $packageCustomsParams = isset($packageParams['customs']) ? $packageParams['customs'] : [];

        //FIXME(nr): should declared value include tax?
        $package = $this->packageFactory->create([
            'packageId'     => $packageId,
            'weight'        => $weight,
            'dimensions'    => $dimensions,
            'declaredValue' => $declaredValue,
            'termsOfTrade'=> isset($packageCustomsParams['terms_of_trade']) ? $packageCustomsParams['terms_of_trade'] : '',
            'additionalFee' => isset($packageCustomsParams['additional_fee']) ? $packageCustomsParams['additional_fee'] : '',
            'placeOfCommital' => isset($packageCustomsParams['place_of_commital']) ? $packageCustomsParams['place_of_commital'] : '',
            'permitNumber' => isset($packageCustomsParams['permit_number']) ? $packageCustomsParams['permit_number'] : '',
            'attestationNumber' => isset($packageCustomsParams['attestation_number']) ? $packageCustomsParams['attestation_number'] : '',
            'exportNotification' => isset($packageCustomsParams['export_notification']) ? : false,
            'items'         => $packageItems,
        ]);

        return $package;
    }

    /**
     * Convert M2 shipment request to platform independent request object.
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @param string                                   $sequenceNumber
     *
     * @return ShipmentOrderInterface
     * @throws CreateShipmentValidationException
     */
    public function mapShipmentRequest($request, $sequenceNumber)
    {
        $shipmentDetails = $this->getShipmentDetails($request);
        $shipper         = $this->getShipper($request);
        $receiver        = $this->getReceiver($request);
        $returnReceiver  = $this->getReturnReceiver($request);
        $services        = $this->getServices($request);
        $package         = $this->getPackage($request);

        $shipmentOrder = $this->shipmentOrderFactory->create([
            'sequenceNumber'  => $sequenceNumber,
            'shipmentDetails' => $shipmentDetails,
            'shipper'         => $shipper,
            'receiver'        => $receiver,
            'returnReceiver'  => $returnReceiver,
            'services'        => $services,
            'packages'        => [$package],
        ]);

        $shipmentOrder = $this->requestValidator->validateShipmentOrder($shipmentOrder);
        return $shipmentOrder;
    }
}
