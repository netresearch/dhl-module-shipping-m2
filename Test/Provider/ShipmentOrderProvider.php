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
 * @package   Dhl\Shipping\Test\Integration
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Test\Provider;

use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\CustomsDetails\CustomsDetails;

/**
 * ShipmentOrderProvider
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShipmentOrderProvider
{
    /**
     * @return ShipmentOrder
     */
    private static function prepareValidOrder()
    {
        $shipmentDetails = self::getShipmentDetailsObject();
        $shipper         = self::getShipperObject();
        $receiver        = self::getReceiverObject();
        $returnReceiver  = self::getReturnReceiverObject();
        $services        = self::getServicesObject();
        $customsDetails  = self::getCustomDetailsObject();
        $package         = self::getPackageObject();

        $shipmentOrder = new ShipmentOrder(
            '1010',
            $shipmentDetails,
            $shipper,
            $receiver,
            $returnReceiver,
            $services,
            $customsDetails,
            [$package]
        );

        return $shipmentOrder;
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public static function getPackageObject()
    {
        $mockObjectGenerator = new \PHPUnit_Framework_MockObject_Generator();
        $package = $mockObjectGenerator->getMock(
            \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Package::class,
            [],
            [],
            '',
            false
        );

        return $package;
    }

    /**
     * @return ShipmentOrder\CustomsDetails\ExportType
     */
    private static function getExporTypeObject()
    {
        $exportType = new ShipmentOrder\CustomsDetails\ExportType(
            'type',
            'description'
        );

        return  $exportType;
    }


    /**
     * @return CustomsDetails
     */
    private static function getCustomDetailsObject()
    {
        $exportType = self::getExporTypeObject();
        $position   = [];//self::getExportPositionObject();
        $customsDetails = new CustomsDetails(
            'invoiceNumber',
            $exportType,
            'termsOfTrade',
            'placeOfCommitial',
            'additionalFee',
            'permitNumber',
            'attestationNumber',
            'true',
            [$position]
        );

        return $customsDetails;
    }

    /**
     * @return ShipmentOrder\Service\ServiceCollection
     */
    private static function getServicesObject()
    {
        $mockObjectGenerator = new \PHPUnit_Framework_MockObject_Generator();
        $codFactoryMock = $mockObjectGenerator->getMock(
            \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\CodFactory::class,
            [],
            [],
            '',
            false
        );

        $requestServiceFactory = new \Dhl\Shipping\Webservice\RequestServiceFactory($codFactoryMock);
        $services = new \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\ServiceCollection(
            $requestServiceFactory
        );

        return $services;
    }

    /**
     * @return ShipmentOrder\Contact\ReturnReceiver
     */
    private static function getReturnReceiverObject()
    {
        $address = self::getContactAddressObject();
        $returnReceiver = new ShipmentOrder\Contact\ReturnReceiver(
            'Theo',
            ['name'],
            'companyName',
            'phone',
            'email@email.de',
            $address
        );

        return $returnReceiver;
    }

    /**
     * @return ShipmentOrder\Contact\Receiver
     */
    private static function getReceiverObject()
    {
        $address  = self::getContactAddressObject();
        $idCard   = self::getIdCardObject();
        $receiver = new ShipmentOrder\Contact\Receiver(
            'Theo',
            ['name'],
            'companyName',
            'phone',
            'email@email.de',
            $address,
            $idCard
        );

        return $receiver;
    }

    /**
     * @return ShipmentOrder\Contact\IdCard
     */
    private static function getIdCardObject()
    {
        $idCard = new \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\IdCard(
            'type',
            'number'
        );

        return $idCard;
    }

    /**
     * @return ShipmentOrder\Contact\Address
     */
    private static function getContactAddressObject()
    {
        $address =  new \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Contact\Address(
            'street',
            'streetName',
            'streetNumber',
            'addressAddition',
            'postalCode',
            'city',
            'state',
            'DE',
            'dispatchInformation'
        );

        return $address;
    }

    /**
     * @return ShipmentOrder\Contact\Shipper
     */
    private static function getShipperObject()
    {
        $address = self::getContactAddressObject();
        $shipper = new ShipmentOrder\Contact\Shipper(
            'Theo',
            ['name'],
            'companyName',
            'phone',
            'email@email.de',
            $address
        );

        return $shipper;
    }

    /**
     * @return ShipmentOrder\ShipmentDetails\ShipmentDetails
     */
    private static function getShipmentDetailsObject()
    {
        $bankData = new ShipmentOrder\ShipmentDetails\BankData(
            'tester',
            'Sparkasse',
            'DE3123123',
            'WE23423',
            ['note1'],
            'aacountRef'
        );

        $shipmentDetails = new ShipmentOrder\ShipmentDetails\ShipmentDetails(
            true,
            true,
            'product',
            '123',
            '456',
            '345',
            'USXXX1',
            'ref',
            'shipRef',
            'shipmentDate',
            $bankData
        );

        return $shipmentDetails;
    }

    /**
     * @param ShipmentOrder $shipmentOrder
     * @return mixed[]
     */
    private static function prepareExpectation(ShipmentOrder $shipmentOrder)
    {
        $data = [
            'sequenceNumber' => $shipmentOrder->getSequenceNumber(),
        ];

        return $data;
    }

    /**
     * @return mixed[]
     */
    public static function getValidOrder()
    {
        $validOrder = self::prepareValidOrder();
        $expectation = self::prepareExpectation($validOrder);

        $provided = [
            'valid_order' => [$validOrder, $expectation],
        ];

        return $provided;
    }
}
