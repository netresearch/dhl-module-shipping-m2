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
        $package         = self::getPackageObject();

        $shipmentOrder = new ShipmentOrder(
            '1010',
            $shipmentDetails,
            $shipper,
            $receiver,
            $returnReceiver,
            $services,
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

        /** @var \Dhl\Shipping\Webservice\RequestServiceFactory $requestServiceFactoryMock */
        $requestServiceFactoryMock = $mockObjectGenerator->getMock(
            \Dhl\Shipping\Webservice\RequestServiceFactory::class,
            [],
            [],
            '',
            false
        );
        $services = new \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\ServiceCollection(
            $requestServiceFactoryMock
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
            'Shipper Company Name',
            'Theo Shipper Name',
            'Shipper Company Returns Division',
            'Theo Contact Me',
            '1800 FOO',
            'foo@email.de',
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
            'Theo Receiver Company',
            'Theo Receiver Name',
            'Receiver Company Division',
            'Theo Contact Me',
            '1800 BAR',
            'bar@email.de',
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
            ['street'],
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
            'Shipper Company Name',
            'Theo Shipper Name',
            'Shipper Company Division',
            'Theo Contact Me',
            '1800 FOO',
            'foo@email.de',
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
            'PREFX',
            'consignment',
            'ref',
            'returnRef',
            'shipmentDate',
            $bankData,
            ''
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
