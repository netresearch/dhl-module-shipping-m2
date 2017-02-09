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

use \Dhl\Versenden\Api\BcsConfigInterface;
use \Dhl\Versenden\Api\GlConfigInterface;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\Contact\AddressInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\Contact\IdInterfaceFactory;
use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\CustomsDetailsInterfaceFactory;
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
     * @var IdInterfaceFactory
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
     * @var PackageInterfaceFactory
     */
    private $packageFactory;

    /**
     * @var ShipmentOrderInterfaceFactory
     */
    private $shipmentOrderFactory;

    /**
     * AppDataMapper constructor.
     * @param BcsConfigInterface $bcsConfig
     * @param GlConfigInterface $glConfig
     * @param BankDataInterfaceFactory $bankDataFactory
     * @param ShipmentDetailsInterfaceFactory $shipmentDetailsFactory
     * @param ShipmentOrderInterfaceFactory $shipmentOrderFactory
     * @param AddressInterfaceFactory $addressFactory
     * @param IdInterfaceFactory $identityFactory
     * @param ShipperInterfaceFactory $shipperFactory
     * @param ReceiverInterfaceFactory $receiverFactory
     * @param ReturnReceiverInterfaceFactory $returnReceiverFactory
     * @param CustomsDetailsInterfaceFactory $customsDetailsFactory
     * @param PackageInterfaceFactory $packageFactory
     */
    public function __construct(
        BcsConfigInterface $bcsConfig,
        GlConfigInterface $glConfig,
        BankDataInterfaceFactory $bankDataFactory,
        ShipmentDetailsInterfaceFactory $shipmentDetailsFactory,
        ShipmentOrderInterfaceFactory $shipmentOrderFactory,
        AddressInterfaceFactory $addressFactory,
        IdInterfaceFactory $identityFactory,
        ShipperInterfaceFactory $shipperFactory,
        ReceiverInterfaceFactory $receiverFactory,
        ReturnReceiverInterfaceFactory $returnReceiverFactory,
        CustomsDetailsInterfaceFactory $customsDetailsFactory,
        PackageInterfaceFactory $packageFactory
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
        $this->packageFactory = $packageFactory;
    }

    /**
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrder\ShipmentDetailsInterface
     */
    private function getShipmentDetails(\Magento\Shipping\Model\Shipment\Request $request)
    {
        $storeId = $request->getOrderShipment()->getStoreId();

        return $this->shipmentDetailsFactory->create([
            //TODO(nr): read from shipment request or config
            'isPrintOnlyIfCodeable' => $this->bcsConfig->isPrintOnlyIfCodeable($storeId), //TODO(nr): override with packaging settings
            'product' => 'V01PAK',
            'accountNumber' => '22222222220101',
            'returnShipmentAccountNumber' => '22222222220701',
            'pickupAccountNumber' => null,
            'reference' => null,
            'returnShipmentReference' => null,
            'shipmentDate' => date("Y-m-d"), // TODO(nr): convert to CET when sending out to BCS API
            'bankData' => null,
        ]);
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
        $storeId = $request->getOrderShipment()->getStoreId();

        $shipmentDetails = $this->getShipmentDetails($request);

        $shipperAddress = $this->addressFactory->create([
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
            'address' => $shipperAddress,
        ]);

        $receiverAddress = $this->addressFactory->create([
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
            'address' => $receiverAddress,

        ]);

        $returnReceiver = $this->returnReceiverFactory->create([

        ]);

        $customsDetails = $this->customsDetailsFactory->create([

        ]);

        $packages = [];
        foreach ($request->getData('packages') as $package) {
            $packages[]= $this->packageFactory->create([

            ]);
        }

        $services = [];

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
