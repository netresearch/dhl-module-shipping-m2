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
namespace Dhl\Shipping\Model\Config;

use Dhl\Shipping\Bcs\CreateShipmentOrderResponse;
use Dhl\Shipping\Webservice\CreateShipmentStatusException;
use \Magento\TestFramework\ObjectManager;

/**
 * ConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class BcsAdapterPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /** @var   \Dhl\Shipping\Webservice\Adapter\BcsAdapter */
    private $bcsAdapterMock;

    public function setUp()
    {
        parent::setUp();
        $this->objectManager = ObjectManager::getInstance();
    }


    /**
     * @test
     */
    public function createLabels()
    {
//        $this->markTestIncomplete('not finished yet');

        $soapClientMock = $this->getMock(
            \Dhl\Shipping\Webservice\Client\BcsSoapClient::class,
            ['createShipmentOrders'],
            [],
            '',
            false
        );

        $CreateShipmentStatusExceptionMock = $this->getMock(
            \Dhl\Shipping\Webservice\CreateShipmentStatusException::class,
            [],
            [],
            '',
            false
        );

        $soapClientMock
            ->expects($this->once())
            ->method('createShipmentOrders')
            ->willThrowException($CreateShipmentStatusExceptionMock);

        $this->bcsAdapter = $this->objectManager->create(
            \Dhl\Shipping\Webservice\Adapter\BcsAdapter::class,
            [
                'soapClient' => $soapClientMock
            ]
        );

        $test = $this->getMock(
            \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder::class,
            [],
            [],
            '',
            false
        );

        $result = $this->bcsAdapter->createLabels([$test]);
        $this->assertTrue($result);
    }

}
