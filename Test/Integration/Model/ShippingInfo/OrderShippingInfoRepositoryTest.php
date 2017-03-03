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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Shipping;

use Dhl\Shipping\Model\ShippingInfo\OrderShippingInfo;
use Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoRepository;
use \Magento\TestFramework\ObjectManager;
use \Dhl\Shipping\Model\ResourceModel\ShippingInfo\OrderShippingInfo as ShippingInfoResource;

/**
 * ConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class OrderShippingInfoRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /** @var  OrderShippingInfoRepository */
    private $model;


    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();


    }

    /**
     * @test
     */
    public function getById()
    {
        /** @var  OrderShippingInfo $orderShippingInfo */
        $orderShippingInfo = $this->objectManager->create(OrderShippingInfo::class);
        $orderShippingInfo->setAddressId(23);
        $orderShippingInfo->setInfo('info');
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(\Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoFactory::class, ['create'], [], '', false);

        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($orderShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($orderShippingInfo);

        $this->objectManager->addSharedInstance($factoryMock, \Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoFactory::class);
        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\OrderShippingInfo::class);

        $this->model  = $this->objectManager->create(OrderShippingInfoRepository::class);

        $result = $this->model->getById(23);
        $this->assertEquals('info', $result->getInfo());
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoFactory::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\OrderShippingInfo::class);
    }

    /**
     * @test
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Shipment with id "23" does not exist.
     */
    public function getByIdExceptionCase()
    {
        /** @var  OrderShippingInfo $orderShippingInfo */
        $orderShippingInfo = $this->objectManager->create(OrderShippingInfo::class);
        $orderShippingInfo->setAddressId(null);
        $orderShippingInfo->setInfo('info');
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(\Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoFactory::class, ['create'], [], '', false);

        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($orderShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($orderShippingInfo);

        $this->objectManager->addSharedInstance($factoryMock, \Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoFactory::class);
        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\OrderShippingInfo::class);
        $this->model  = $this->objectManager->create(OrderShippingInfoRepository::class);

        $result = $this->model->getById(23);

        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoFactory::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\OrderShippingInfo::class);
    }

}
