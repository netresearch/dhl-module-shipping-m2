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
namespace Dhl\Shipping\Util;

use Dhl\Shipping\Util\Serializer\SerializerInterface;
use Dhl\Shipping\Gla\Request\LabelRequest;
use Dhl\Shipping\Gla\Request\Type\ConsigneeAddressRequestType;
use Dhl\Shipping\Gla\Request\Type\PackageDetailsRequestType;
use Dhl\Shipping\Gla\Request\Type\PackageRequestType;
use Dhl\Shipping\Gla\Request\Type\ShipmentRequestType;
use Dhl\Shipping\Gla\Response\LabelResponse;
use Dhl\Shipping\Gla\Response\Type\LabelDetailsResponseType;
use Dhl\Shipping\Gla\Response\Type\PackageResponseType;
use Dhl\Shipping\Gla\Response\Type\ShipmentResponseType;
use \Magento\Framework\Filesystem\Driver\File as Filesystem;
use \Magento\TestFramework\ObjectManager;

/**
 * JsonSerializerTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class JsonSerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /**
     * prepare object manager
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @return string[]
     */
    public function getJsonResponseDataProvider()
    {
        $driver = new Filesystem();
        return [
            'single_label_response' => [
                $driver->fileGetContents(__DIR__ . '/../_files/glSingleLabelResponse.json')
            ]
        ];
    }

    /**
     * @test
     */
    public function serializeJson()
    {
        $pickupAccount = '4cc0un7';
        $distributionCenter = 'f00123';
        $consignmentNumber = '80000000000';
        $consigneeAddress1 = '123 Foo Street';
        $consigneeCity = 'Foo Valley';
        $consigneeCountry = 'US';
        $consigneePhone = '1 800';
        $packageDetailsCurrency = 'USD';
        $packageDetailsOrderedProduct = 'BMD';
        $packageDetailsPackageId = '3000000001-1';
        $packageDetailsWeight = '1200';
        $packageDetailsWeightUom = 'G';

        $jsonRequest = <<<JSON
{
  "shipments": [
    {
      "pickupAccount": "$pickupAccount",
      "distributionCenter": "$distributionCenter",
      "packages": [
        {
          "consigneeAddress": {
            "address1": "$consigneeAddress1",
            "city": "$consigneeCity",
            "country": "$consigneeCountry",
            "phone": "$consigneePhone"
          },
          "packageDetails": {
            "currency": "$packageDetailsCurrency",
            "orderedProduct": "$packageDetailsOrderedProduct",
            "packageId": "$packageDetailsPackageId",
            "weight": "$packageDetailsWeight",
            "weightUom": "$packageDetailsWeightUom"
          }
        }
      ],
      "consignmentNumber": "$consignmentNumber"
    }
  ]
}
JSON;
        $jsonRequest = preg_replace("/\s+/", "", $jsonRequest);
        $shipmentData = json_decode($jsonRequest, true);

        $consigneeAddress = $this->objectManager->create(ConsigneeAddressRequestType::class, [
            'address1' => $shipmentData['shipments'][0]['packages'][0]['consigneeAddress']['address1'],
            'city' => $shipmentData['shipments'][0]['packages'][0]['consigneeAddress']['city'],
            'country' => $shipmentData['shipments'][0]['packages'][0]['consigneeAddress']['country'],
            'phone' => $shipmentData['shipments'][0]['packages'][0]['consigneeAddress']['phone'],
        ]);
        $packageDetails = $this->objectManager->create(PackageDetailsRequestType::class, [
            'currency' => $shipmentData['shipments'][0]['packages'][0]['packageDetails']['currency'],
            'orderedProduct' => $shipmentData['shipments'][0]['packages'][0]['packageDetails']['orderedProduct'],
            'packageId' => $shipmentData['shipments'][0]['packages'][0]['packageDetails']['packageId'],
            'weight' => $shipmentData['shipments'][0]['packages'][0]['packageDetails']['weight'],
            'weightUom' => $shipmentData['shipments'][0]['packages'][0]['packageDetails']['weightUom'],
        ]);
        $package = $this->objectManager->create(PackageRequestType::class, [
            'consigneeAddress' => $consigneeAddress,
            'packageDetails' => $packageDetails,
        ]);

        $shipment = $this->objectManager->create(ShipmentRequestType::class, [
            'pickupAccount' => $shipmentData['shipments'][0]['pickupAccount'],
            'distributionCenter' => $shipmentData['shipments'][0]['distributionCenter'],
            'packages' => [$package],
            'consignmentNumber' => $shipmentData['shipments'][0]['consignmentNumber'],
        ]);
        $labelRequest = $this->objectManager->create(LabelRequest::class, [
            'shipments' => [$shipment],
        ]);
        $labelRequest = json_encode($labelRequest);

        $this->assertSame($jsonRequest, $labelRequest);
    }

    /**
     * @test
     * @dataProvider getJsonResponseDataProvider
     * @param $jsonResponse
     */
    public function unserializeJson($jsonResponse)
    {
        /** @var SerializerInterface $builder */
        $serializer = $this->objectManager->get(SerializerInterface::class);
        $labelResponse = $serializer->deserialize($jsonResponse, LabelResponse::class);
        $this->assertInstanceOf(LabelResponse::class, $labelResponse);

        /** @var LabelResponse $labelResponse */
        $shipments = $labelResponse->getShipments();
        $this->assertInternalType('array', $shipments);
        $this->assertContainsOnly(ShipmentResponseType::class, $shipments);

        foreach ($shipments as $shipment) {
            $packages = $shipment->getPackages();
            $this->assertInternalType('array', $packages);
            $this->assertContainsOnly(PackageResponseType::class, $packages);

            foreach ($packages as $package) {
                $labelDetails = $package->getResponseDetails()->getLabelDetails();
                $this->assertInternalType('array', $labelDetails);
                $this->assertContainsOnly(LabelDetailsResponseType::class, $labelDetails);

                foreach ($labelDetails as $labelDetail) {
                    $this->assertStringEndsWith('-1', $labelDetail->getPackageId());
                    $this->assertEquals('pdf', $labelDetail->getFormat());
                    $this->assertStringStartsWith('%PDF', $labelDetail->getLabelData());
                }
            }
        }
    }
}
