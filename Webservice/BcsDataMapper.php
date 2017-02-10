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

use \Dhl\Versenden\Api\Data\Webservice\Request;
use \Dhl\Versenden\Api\Webservice\Request\Mapper\BcsDataMapperInterface;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder;
use \Dhl\Versenden\Bcs as BcsApi;

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
     * @param ShipmentOrder\ShipmentDetailsInterface $shipmentDetails
     * @param ShipmentOrder\PackageInterface[] $packages
     * @return BcsApi\ShipmentDetailsTypeType
     */
    private function getShipmentDetails(ShipmentOrder\ShipmentDetailsInterface $shipmentDetails, array $packages)
    {
        // bcs cannot handle multiple packages
        $package = current($packages);

        //TODO(nr): convert to KG
        $shipmentItemType = new BcsApi\ShipmentItemType($package->getWeight()->getValue());

        //TODO(nr): convert to CM
        $shipmentItemType->setWidthInCM($package->getDimensions()->getWidth());
        $shipmentItemType->setHeightInCM($package->getDimensions()->getHeight());
        $shipmentItemType->setLengthInCM($package->getDimensions()->getLength());

        $shipmentDetailsType = new BcsApi\ShipmentDetailsTypeType(
            $shipmentDetails->getProduct(),
            $shipmentDetails->getAccountNumber(),
            $shipmentDetails->getShipmentDate(), // TODO(nr): convert to CET
            $shipmentItemType
        );
        return $shipmentDetailsType;
    }

    /**
     * @param ShipmentOrder\ShipperInterface $shipper
     * @return BcsApi\ShipperType
     */
    private function getShipper(ShipmentOrder\ShipperInterface $shipper)
    {
        // shipper name
        $shipperName = $shipper->getName();
        $nameType = new BcsApi\NameType($shipperName[0], $shipperName[1], $shipperName[2]);

        // shipper address
        $countryType = new BcsApi\CountryType($shipper->getAddress()->getCountryCode());
        //TODO(nr): obtain country name
        $countryType->setCountry($shipper->getAddress()->getCountryCode());
        $countryType->setState($shipper->getAddress()->getState());
        //TODO(nr): split address
        $shipperStreet = $shipper->getAddress()->getStreet();
        $shipperStreetNumber = $shipper->getAddress()->getStreet();
        $addressType = new BcsApi\NativeAddressType(
            $shipperStreet,
            $shipperStreetNumber,
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
     * @param ShipmentOrder\ReceiverInterface $receiver
     * @return BcsApi\ReceiverType
     */
    private function getReceiver(ShipmentOrder\ReceiverInterface $receiver)
    {
        $receiverName = $receiver->getName();

        // receiver address
        $countryType = new BcsApi\CountryType($receiver->getAddress()->getCountryCode());
        //TODO(nr): obtain country name
        $countryType->setCountry($receiver->getAddress()->getCountryCode());
        $countryType->setState($receiver->getAddress()->getState());
        //TODO(nr): split address
        $receiverStreet = $receiver->getAddress()->getStreet();
        $receiverStreetNumber = $receiver->getAddress()->getStreet();
        $receiverStreetSupplement = '';

        $addressType = new BcsApi\ReceiverNativeAddressType(
            $receiverName[1],
            $receiverName[2],
            $receiverStreet,
            $receiverStreetNumber,
            $receiver->getAddress()->getPostalCode(),
            $receiver->getAddress()->getCity(),
            $countryType
        );
        $addressType->setAddressAddition([$receiverStreetSupplement]);

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
     * @param ShipmentOrder\ReturnReceiverInterface $returnReceiver
     * @return BcsApi\ShipperType
     */
    private function getReturnReceiver(ShipmentOrder\ReturnReceiverInterface $returnReceiver)
    {
        // return receiver name
        $shipperName = $returnReceiver->getName();
        $nameType = new BcsApi\NameType($shipperName[0], $shipperName[1], $shipperName[2]);

        // return receiver address
        $countryType = new BcsApi\CountryType($returnReceiver->getAddress()->getCountryCode());
        //TODO(nr): obtain country name
        $countryType->setCountry($returnReceiver->getAddress()->getCountryCode());
        $countryType->setState($returnReceiver->getAddress()->getState());
        //TODO(nr): split address
        $shipperStreet = $returnReceiver->getAddress()->getStreet();
        $shipperStreetNumber = $returnReceiver->getAddress()->getStreet();
        $addressType = new BcsApi\NativeAddressType(
            $shipperStreet,
            $shipperStreetNumber,
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
     * @param ShipmentOrder\CustomsDetailsInterface $customsDetails
     * @return BcsApi\ExportDocumentType
     */
    private function getExportDocument(ShipmentOrder\CustomsDetailsInterface $customsDetails)
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
                //TODO(nr): convert to KG
                $position->getWeight()->getValue(),
                //TODO(nr): convert to EUR
                $position->getDeclaredValue()->getValue()
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
     * @param \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrderInterface $shipmentOrder
     * @return BcsApi\ShipmentOrderType
     */
    public function mapShipmentOrder(Request\Type\CreateShipment\ShipmentOrderInterface $shipmentOrder)
    {
        $shipmentDetailsType = $this->getShipmentDetails($shipmentOrder->getShipmentDetails(), $shipmentOrder->getPackages());
        $shipperType = $this->getShipper($shipmentOrder->getShipper());
        $receiverType = $this->getReceiver($shipmentOrder->getReceiver());
        $returnReceiverType = $this->getReturnReceiver($shipmentOrder->getReturnReceiver());
        $exportDocumentType = $this->getExportDocument($shipmentOrder->getCustomsDetails());

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
        return $shipmentOrderType;
    }

    /**
     * Create api specific request object from framework standardized object.
     *
     * @param \Dhl\Versenden\Api\Data\Webservice\Request\Type\GetVersionRequestInterface $request
     * @return \Dhl\Versenden\Bcs\Version
     */
    public function mapVersion(Request\Type\GetVersionRequestInterface $request)
    {
        // TODO: Implement mapVersion() method.
    }

    /**
     * Create api specific request object from framework standardized object.
     * TODO(nr): shipment numbers are a simple type, no need to convert something?
     *
     * @param \Dhl\Versenden\Api\Data\Webservice\Request\Type\DeleteShipmentRequestInterface[] $numbers
     * @return string[]
     */
    public function mapShipmentNumbers(array $numbers)
    {
        // TODO: Implement mapShipmentNumbers() method.
    }

}
