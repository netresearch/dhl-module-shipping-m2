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

use Dhl\Shipping\Webservice\RequestType;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\PackageInterface;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\AbstractServiceFactory;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\ServiceCollectionInterface;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\ShipmentDetails;
use Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\ShipmentDetails\ShipmentDetailsInterface;
use Dhl\Shipping\Webservice\RequestMapper\GlDataMapperInterface;
use Dhl\Shipping\Gla\Request\Type\ConsigneeAddressRequestType;
use Dhl\Shipping\Gla\Request\Type\CustomsDetailsRequestType;
use Dhl\Shipping\Gla\Request\Type\PackageDetailsRequestType;
use Dhl\Shipping\Gla\Request\Type\PackageRequestType;
use Dhl\Shipping\Gla\Request\Type\ReturnAddressRequestType;
use Dhl\Shipping\Gla\Request\Type\ShipmentRequestType;

/**
 * GlDataMapper
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Paul Siedler <paul.siedler@netresearch.de>
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
        \Zend_Measure_Weight::POUND => 'LB',
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
     * @param ShipmentDetailsInterface $shipmentDetails
     * @param ServiceCollectionInterface $services
     * @param PackageInterface $package
     * @param string $sequenceNumber
     * @return PackageDetailsRequestType
     */
    private function getPackageDetails(
        ShipmentDetailsInterface $shipmentDetails,
        ServiceCollectionInterface $services,
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

        /** @var \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\Cod $codService */
        $codService = $services->getService(AbstractServiceFactory::SERVICE_CODE_COD);
        if ($codService) {
            $codAmount = $codService->getCodAmount()->getValue($currencyCode);
        } else {
            $codAmount = null;
        }

        $packageDetailsType = new PackageDetailsRequestType(
            $package->getDeclaredValue()->getCurrencyCode(),
            $shipmentDetails->getProduct(),
            $this->getPackageId($shipmentDetails, $sequenceNumber),
            $package->getWeight()->getValue($package->getWeight()->getUnitOfMeasurement()),
            $weightUom,
            null,
            null,
            $codAmount,
            $package->getDeclaredValue()->getValue($currencyCode),
            null,
            $package->getDangerousGoodsCategory(),
            $dimensionUom,
            $package->getDimensions()->getHeight($package->getDimensions()->getUnitOfMeasurement()),
            $package->getDimensions()->getLength($package->getDimensions()->getUnitOfMeasurement()),
            $package->getDimensions()->getWidth($package->getDimensions()->getUnitOfMeasurement()),
            $package->getTermsOfTrade(),
            null,
            null,
            $package->getExportDescription(),
            $sequenceNumber,
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
            $receiver->getName(),
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

        $returnReceiverType = new ReturnAddressRequestType(
            $street[0],
            $returnReceiver->getAddress()->getCity(),
            $returnReceiver->getAddress()->getCountryCode(),
            $returnReceiver->getAddress()->getState(),
            $street[1],
            null,
            $returnReceiver->getCompanyName(),
            $returnReceiver->getName(),
            $returnReceiver->getAddress()->getPostalCode()
        );

        return $returnReceiverType;
    }

    /**
     * @param PackageInterface $package
     * @return CustomsDetailsRequestType[]
     */
    private function getExportDocument(PackageInterface $package)
    {
        $customsDetailsTypes = [];

        $currencyCode = $package->getDeclaredValue()
                                ->getCurrencyCode();
        /** @var RequestType\CreateShipment\ShipmentOrder\Package\PackageItemInterface $packageItem */
        foreach ($package->getItems() as $packageItem) {
            // TODO(nr) should we check shipping routes for crossborder instead?
            if ($packageItem->getCustomsItemDescription()) {
                $itemDetails = new CustomsDetailsRequestType(
                    $packageItem->getCustomsItemDescription(),
                    '',
                    '',
                    $packageItem->getItemOriginCountry(),
                    $packageItem->getTariffNumber(),
                    (int)$packageItem->getQty(),
                    $packageItem->getCustomsValue()->getValue($currencyCode),
                    $packageItem->getSku()
                );
                $customsDetailsTypes[] = $itemDetails;
            }
        }

        return $customsDetailsTypes;
    }

    /**
     * Create api specific request object from framework standardized object.
     *
     * @param RequestType\CreateShipment\ShipmentOrderInterface $shipmentOrder
     * @return ShipmentRequestType The "GL API shipment" entity
     */
    public function mapShipmentOrder(RequestType\CreateShipment\ShipmentOrderInterface $shipmentOrder)
    {
        $packageTypes = [];

        $receiverType = $this->getReceiver($shipmentOrder->getReceiver());
        $returnReceiverType = $this->getReturnReceiver($shipmentOrder->getReturnReceiver());

        foreach ($shipmentOrder->getPackages() as $package) {
            $customsDetailsType = $this->getExportDocument($package);
            $packageDetailsType = $this->getPackageDetails(
                $shipmentOrder->getShipmentDetails(),
                $shipmentOrder->getServices(),
                $package,
                $shipmentOrder->getSequenceNumber()
            );
            $packageType = new PackageRequestType(
                $receiverType,
                $packageDetailsType,
                $returnReceiverType,
                $customsDetailsType
            );
            $packageTypes[] = $packageType;
        }

        $shipmentType = new ShipmentRequestType(
            $shipmentOrder->getShipmentDetails()->getPickupAccountNumber(),
            $shipmentOrder->getShipmentDetails()->getDistributionCenter(),
            $packageTypes,
            $shipmentOrder->getShipmentDetails()->getConsignmentNumber()
        );

        return $shipmentType;
    }

    /**
     * Generate unique packageId
     *
     * @param ShipmentDetails\ShipmentDetailsInterface $shipmentDetails
     * @param string $sequenceNumber
     * @return string
     */
    private function getPackageId(ShipmentDetails\ShipmentDetailsInterface $shipmentDetails, $sequenceNumber)
    {
        $time = time() - 946684800; // time since 2001
        $uniquePackageId = $shipmentDetails->getCustomerPrefix() . $sequenceNumber . $time;

        // remove non-alphanum chars from package id
        $uniquePackageId = preg_replace('/[^a-zA-Z\d]/', '', $uniquePackageId);

        return $uniquePackageId;
    }
}
