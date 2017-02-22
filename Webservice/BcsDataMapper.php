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

use \Dhl\Versenden\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrderInterface;
use \Dhl\Versenden\Api\Data\Webservice\RequestType\GetVersionRequestInterface;
use \Dhl\Versenden\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\PackageInterface;
use \Dhl\Versenden\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact;
use \Dhl\Versenden\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\CustomsDetails;
use \Dhl\Versenden\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\Service;
use \Dhl\Versenden\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\ShipmentDetails;
use \Dhl\Versenden\Api\Webservice\RequestMapper\BcsDataMapperInterface;
use \Dhl\Versenden\Bcs as BcsApi;
use \Dhl\Versenden\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\AbstractServiceFactory;

/**
 * BcsDataMapper
 *
 * @category Dhl
 * @package  Dhl\Versenden\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 *
 * @SuppressWarnings(MEQP2.Classes.ObjectInstantiation)
 */
class BcsDataMapper implements BcsDataMapperInterface
{
    /**
     * @param ShipmentDetails\ShipmentDetailsInterface $shipmentDetails
     * @param PackageInterface[] $packages
     * @return BcsApi\ShipmentDetailsTypeType
     */
    private function getShipmentDetails(ShipmentDetails\ShipmentDetailsInterface $shipmentDetails, array $packages)
    {
        // bcs cannot handle multiple packages
        $package = current($packages);

        $packageWeight = $package->getWeight()->getValue(\Zend_Measure_Weight::KILOGRAM);
        $shipmentItemType = new BcsApi\ShipmentItemType($packageWeight);
        $shipmentItemType->setWidthInCM($package->getDimensions()->getWidth(\Zend_Measure_Length::CENTIMETER));
        $shipmentItemType->setHeightInCM($package->getDimensions()->getHeight(\Zend_Measure_Length::CENTIMETER));
        $shipmentItemType->setLengthInCM($package->getDimensions()->getLength(\Zend_Measure_Length::CENTIMETER));

        $shipmentDetailsType = new BcsApi\ShipmentDetailsTypeType(
            $shipmentDetails->getProduct(),
            $shipmentDetails->getAccountNumber(),
            $shipmentDetails->getShipmentDate(), // TODO(nr): convert to CET
            $shipmentItemType
        );

        return $shipmentDetailsType;
    }

    /**
     * @param Service\ServiceCollectionInterface $services
     * @return BcsApi\ShipmentService
     */
    private function getServices(Service\ServiceCollectionInterface $services)
    {
        $serviceType = new BcsApi\ShipmentService();

        /** @var \Dhl\Versenden\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\Cod $codService */
        $codService = $services->getService(AbstractServiceFactory::SERVICE_CODE_COD);
        if ($codService) {
            $codConfig = new BcsApi\ServiceconfigurationCashOnDelivery(
                true,
                $codService->addFee(),
                $codService->getCodAmount()->getValue('EUR')
            );
            $serviceType->setCashOnDelivery($codConfig);
        };

        return $serviceType;
    }

    /**
     * @param Contact\ShipperInterface $shipper
     * @return BcsApi\ShipperType
     */
    private function getShipper(Contact\ShipperInterface $shipper)
    {
        // shipper name
        $shipperName = $shipper->getName();
        $nameType = new BcsApi\NameType($shipperName[0], $shipperName[1], $shipperName[2]);

        // shipper address
        $countryType = new BcsApi\CountryType($shipper->getAddress()->getCountryCode());
        //TODO(nr): obtain country name
        $countryType->setCountry($shipper->getAddress()->getCountryCode());
        $countryType->setState($shipper->getAddress()->getState());

        $addressType = new BcsApi\NativeAddressType(
            $shipper->getAddress()->getStreetName(),
            $shipper->getAddress()->getStreetNumber(),
            $shipper->getAddress()->getPostalCode(),
            $shipper->getAddress()->getCity(),
            $countryType
        );

        // shipper communication
        $communicationType = new BcsApi\CommunicationType();
        $communicationType->setContactPerson($shipper->getContactPerson());
        $communicationType->setEmail($shipper->getEmail());
        $communicationType->setPhone($shipper->getPhone());

        $shipperType = new BcsApi\ShipperType($nameType, $addressType, $communicationType);
        return $shipperType;
    }

    /**
     * @param Contact\ReceiverInterface $receiver
     * @return BcsApi\ReceiverType
     */
    private function getReceiver(Contact\ReceiverInterface $receiver)
    {
        $receiverName = $receiver->getName();

        // receiver address
        $countryType = new BcsApi\CountryType($receiver->getAddress()->getCountryCode());
        //TODO(nr): obtain country name
        $countryType->setCountry($receiver->getAddress()->getCountryCode());
        $countryType->setState($receiver->getAddress()->getState());

        $addressType = new BcsApi\ReceiverNativeAddressType(
            $receiverName[0],
            $receiverName[1],
            $receiver->getAddress()->getStreetName(),
            $receiver->getAddress()->getStreetNumber(),
            $receiver->getAddress()->getPostalCode(),
            $receiver->getAddress()->getCity(),
            $countryType
        );
        $addressType->setAddressAddition([$receiver->getAddress()->getAddressAddition()]);

        // receiver communication
        $communicationType = new BcsApi\CommunicationType();
        $communicationType->setContactPerson($receiver->getContactPerson());
        $communicationType->setEmail($receiver->getEmail());
        $communicationType->setPhone($receiver->getPhone());

        $receiverType = new BcsApi\ReceiverType(
            $receiverName[0],
            $addressType,
            null, //TODO(nr): handle postal facilities
            null,
            null,
            $communicationType
        );
        return $receiverType;
    }

    /**
     * @param Contact\ReturnReceiverInterface $returnReceiver
     * @return BcsApi\ShipperType
     */
    private function getReturnReceiver(Contact\ReturnReceiverInterface $returnReceiver)
    {
        // return receiver name
        $shipperName = $returnReceiver->getName();
        $nameType = new BcsApi\NameType($shipperName[0], $shipperName[1], $shipperName[2]);

        // return receiver address
        $countryType = new BcsApi\CountryType($returnReceiver->getAddress()->getCountryCode());
        //TODO(nr): obtain country name
        $countryType->setCountry($returnReceiver->getAddress()->getCountryCode());
        $countryType->setState($returnReceiver->getAddress()->getState());

        $addressType = new BcsApi\NativeAddressType(
            $returnReceiver->getAddress()->getStreetName(),
            $returnReceiver->getAddress()->getStreetNumber(),
            $returnReceiver->getAddress()->getPostalCode(),
            $returnReceiver->getAddress()->getCity(),
            $countryType
        );

        // shipper communication
        $communicationType = new BcsApi\CommunicationType();
        $communicationType->setContactPerson($returnReceiver->getContactPerson());
        $communicationType->setEmail($returnReceiver->getEmail());
        $communicationType->setPhone($returnReceiver->getPhone());

        $shipperType = new BcsApi\ShipperType($nameType, $addressType, $communicationType);
        return $shipperType;
    }

    /**
     * @param CustomsDetails\CustomsDetailsInterface $customsDetails
     * @return BcsApi\ExportDocumentType
     */
    private function getExportDocument(CustomsDetails\CustomsDetailsInterface $customsDetails)
    {
        $exportDocumentType = new BcsApi\ExportDocumentType(
            $customsDetails->getExportType()->getType(),
            $customsDetails->getPlaceOfCommital(),
            $customsDetails->getAdditionalFee()
        );

        $exportDocumentType->setInvoiceNumber($customsDetails->getInvoiceNumber());
        $exportDocumentType->setExportTypeDescription($customsDetails->getExportType()->getDescription());
        $exportDocumentType->setTermsOfTrade($customsDetails->getTermsOfTrade());
        $exportDocumentType->setPermitNumber($customsDetails->getPermitNumber());
        $exportDocumentType->setAttestationNumber($customsDetails->getAttestationNumber());
        $exportDocumentType->setWithElectronicExportNtfctn(
            new BcsApi\Serviceconfiguration($customsDetails->isWithElectronicExportNtfctn())
        );

        $exportDocPositions = [];
        foreach ($customsDetails->getPositions() as $position) {
            $exportDocPosition = new BcsApi\ExportDocPosition(
                $position->getItemDescription(),
                $position->getCountryOfOrigin(),
                $position->getHsCode(),
                $position->getQty(),
                $position->getWeight()->getValue(\Zend_Measure_Weight::KILOGRAM),
                $position->getDeclaredValue()->getValue('EUR')
            );
            $exportDocPositions[]= $exportDocPosition;
        }

        $exportDocumentType->setExportDocPosition($exportDocPositions);

        return null;

        //TODO(nr): return customs details (only if applicable)
        return $exportDocumentType;
    }

    /**
     * Create api specific request object from framework standardized object.
     *
     * @param ShipmentOrderInterface $shipmentOrder
     * @return BcsApi\ShipmentOrderType
     */
    public function mapShipmentOrder(ShipmentOrderInterface $shipmentOrder)
    {
        // account data, package definition, carrier product, additional services
        $shipmentDetailsType = $this->getShipmentDetails(
            $shipmentOrder->getShipmentDetails(),
            $shipmentOrder->getPackages()
        );
        $serviceType = $this->getServices($shipmentOrder->getServices());
        $shipmentDetailsType->setService($serviceType);

        // shipper, receiver, return receiver
        $shipperType = $this->getShipper($shipmentOrder->getShipper());
        $receiverType = $this->getReceiver($shipmentOrder->getReceiver());
        $returnReceiverType = $this->getReturnReceiver($shipmentOrder->getReturnReceiver());

        // customs declaration
        $exportDocumentType = $this->getExportDocument($shipmentOrder->getCustomsDetails());


        // shipment definition, label format, print only if codeable
        $shipmentType = new BcsApi\Shipment(
            $shipmentDetailsType,
            $shipperType,
            $receiverType,
            $returnReceiverType,
            $exportDocumentType
        );

        $shipmentOrderType = new BcsApi\ShipmentOrderType(
            $shipmentOrder->getSequenceNumber(),
            $shipmentType
        );

        $printOnlyIfCodeable = new BcsApi\Serviceconfiguration(
            $shipmentOrder->getShipmentDetails()->isPrintOnlyIfCodeable()
        );
        $shipmentOrderType->setLabelResponseType('B64');
        $shipmentOrderType->setPrintOnlyIfCodeable($printOnlyIfCodeable);

        return $shipmentOrderType;
    }

    /**
     * Create api specific request object from framework standardized object.
     *
     * @param GetVersionRequestInterface $request
     * @return \Dhl\Versenden\Bcs\Version
     */
    public function mapVersion(GetVersionRequestInterface $request)
    {
        // TODO: Implement mapVersion() method.
    }

    /**
     * Create api specific request object from framework standardized object.
     * TODO(nr): shipment numbers are a simple type, no need to convert something?
     *
     * @param \Dhl\Versenden\Api\Data\Webservice\RequestType\DeleteShipmentRequestInterface[] $numbers
     * @return string[]
     */
    public function mapShipmentNumbers(array $numbers)
    {
        // TODO: Implement mapShipmentNumbers() method.
    }

}
