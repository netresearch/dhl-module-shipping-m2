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

use Dhl\Shipping\Api\Util\Serializer\SerializerInterface;
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
class JsonSerializerTest extends \PHPUnit_Framework_TestCase
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
