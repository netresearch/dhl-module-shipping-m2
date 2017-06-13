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

use \Dhl\Shipping\Api\Data\Webservice\RequestType;
use \Dhl\Shipping\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\PackageInterface;
use \Dhl\Shipping\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact;
use \Dhl\Shipping\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\CustomsDetails;
use \Dhl\Shipping\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\Service;
use \Dhl\Shipping\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrder\ShipmentDetails;
use \Dhl\Shipping\Api\Webservice\RequestMapper\GlDataMapperInterface;
use Dhl\Shipping\Gla\Request\Type\ConsigneeAddressRequestType;
use Dhl\Shipping\Gla\Request\Type\CustomsDetailsRequestType;
use \Dhl\Shipping\Gla\Request\Type\PackageDetailsRequestType;
use Dhl\Shipping\Gla\Request\Type\PackageRequestType;
use Dhl\Shipping\Gla\Request\Type\ReturnAddressRequestType;
use Dhl\Shipping\Gla\Request\Type\ShipmentRequestType;

/**
 * GlDataMapper
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 *
 * @todo(nr): move to lib if possible
 * @SuppressWarnings(MEQP2.Classes.ObjectInstantiation)
 */
class GlDataMapper implements GlDataMapperInterface
{
    private $weightUomMap = [
        \Zend_Measure_Weight::GRAM => 'G',
        \Zend_Measure_Weight::KILOGRAM => 'KG',
        \Zend_Measure_Weight::OUNCE => 'OZ',
        \Zend_Measure_Weight::POUND => 'LBS',
    ];

    private $dimensionUomMap = [
        \Zend_Measure_Length::INCH => 'IN',
        \Zend_Measure_Length::CENTIMETER => 'CM',
        \Zend_Measure_Length::MILLIMETER => 'MM',
        \Zend_Measure_Length::FEET => 'FT',
        \Zend_Measure_Length::METER => 'M',
        \Zend_Measure_Length::YARD => 'Y',
    ];

    /**
     * @param ShipmentDetails\ShipmentDetailsInterface $shipmentDetails
     * @param PackageInterface $package
     * @param string $sequenceNumber
     * @return PackageDetailsRequestType
     */
    private function getPackageDetails(
        ShipmentDetails\ShipmentDetailsInterface $shipmentDetails,
        PackageInterface $package,
        $sequenceNumber
    ) {
        // no weight conversions for GLAPI but unit mapping
        $currencyCode = $package->getDeclaredValue()->getCurrencyCode();
        $weightUom = $package->getWeight()->getUnitOfMeasurement();
        if (isset($this->weightUomMap[$weightUom])) {
            $weightUom = $this->weightUomMap[$weightUom];
        }
        $dimensionUom = $package->getDimensions()->getUnitOfMeasurement();
        if (isset($this->dimensionUomMap[$dimensionUom])) {
            $dimensionUom = $this->dimensionUomMap[$dimensionUom];
        }

        $packageDetailsType = new PackageDetailsRequestType(
            $package->getDeclaredValue()->getCurrencyCode(),
            $shipmentDetails->getProduct(),
            $sequenceNumber,
            $package->getWeight()->getValue($package->getWeight()->getUnitOfMeasurement()),
            $weightUom,
            null,
            null,
            null,
            $package->getDeclaredValue()->getValue($currencyCode),
            null,
            null,
            $dimensionUom,
            $package->getDimensions()->getHeight($package->getDimensions()->getUnitOfMeasurement()),
            $package->getDimensions()->getLength($package->getDimensions()->getUnitOfMeasurement()),
            $package->getDimensions()->getWidth($package->getDimensions()->getUnitOfMeasurement()),
            null,
            null,
            null,
            null,
            null,
            null,
            null
        );

        return $packageDetailsType;
    }

    /**
     * @param Contact\ReceiverInterface $receiver
     * @return ConsigneeAddressRequestType
     */
    private function getReceiver(Contact\ReceiverInterface $receiver)
    {
        $street = $receiver->getAddress()->getStreet();
        $name = $receiver->getName();

        $receiverType = new ConsigneeAddressRequestType(
            $street[0],
            $receiver->getAddress()->getCity(),
            $receiver->getAddress()->getCountryCode(),
            $receiver->getPhone(),
            $street[1],
            null,
            $receiver->getCompanyName(),
            $receiver->getEmail(),
            $receiver->getId()->getNumber(),
            $receiver->getId()->getType(),
            $name[0],
            $receiver->getAddress()->getPostalCode(),
            $receiver->getAddress()->getState()
        );

        return $receiverType;
    }

    /**
     * @param Contact\ReturnReceiverInterface $returnReceiver
     * @return ReturnAddressRequestType
     */
    private function getReturnReceiver(Contact\ReturnReceiverInterface $returnReceiver)
    {
        $street = $returnReceiver->getAddress()->getStreet();
        $name = $returnReceiver->getName();

        $returnReceiverType = new ReturnAddressRequestType(
            $street[0],
            $returnReceiver->getAddress()->getCity(),
            $returnReceiver->getAddress()->getCountryCode(),
            $returnReceiver->getAddress()->getState(),
            $street[1],
            null,
            $returnReceiver->getCompanyName(),
            $name[0],
            $returnReceiver->getAddress()->getPostalCode()
        );

        return $returnReceiverType;
    }

    /**
     * @param CustomsDetails\CustomsDetailsInterface $customsDetails
     * @return CustomsDetailsRequestType[]
     */
    private function getExportDocument(CustomsDetails\CustomsDetailsInterface $customsDetails)
    {
        $customsDetailsTypes = [];

        return $customsDetailsTypes;
    }

    /**
     * Create api specific request object from framework standardized object.
     *
     * @param \Dhl\Shipping\Api\Data\Webservice\RequestType\CreateShipment\ShipmentOrderInterface $shipmentOrder
     * @return ShipmentRequestType The "GL API shipment" entity
     */
    public function mapShipmentOrder(RequestType\CreateShipment\ShipmentOrderInterface $shipmentOrder)
    {
        $packageTypes = [];

        $receiverType = $this->getReceiver($shipmentOrder->getReceiver());
        $returnReceiverType = $this->getReturnReceiver($shipmentOrder->getReturnReceiver());
        $customsDetailsType = $this->getExportDocument($shipmentOrder->getCustomsDetails());

        foreach ($shipmentOrder->getPackages() as $package) {
            $packageDetailsType = $this->getPackageDetails(
                $shipmentOrder->getShipmentDetails(),
                $package,
                $shipmentOrder->getSequenceNumber()
            );
            $packageType = new PackageRequestType(
                $receiverType,
                $packageDetailsType,
                $returnReceiverType,
                $customsDetailsType
            );
            $packageTypes[]= $packageType;
        }

        $shipmentType = new ShipmentRequestType(
            $shipmentOrder->getShipmentDetails()->getPickupAccountNumber(),
            $shipmentOrder->getShipmentDetails()->getDistributionCenter(),
            $packageTypes
        );

        return $shipmentType;
    }
}
