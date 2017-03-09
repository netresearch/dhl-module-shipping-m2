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

use \Dhl\Shipping\Model\ResourceModel\ShippingInfo\OrderShippingInfo as ShippingInfoResource;
use \Dhl\Shipping\Model\ShippingInfo\OrderShippingInfo;
use \Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoFactory;
use \Dhl\Shipping\Model\ShippingInfo\OrderShippingInfoRepository;
use \Magento\TestFramework\ObjectManager;

/**
 * OrderShippingInfoRepositoryTest
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
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * TODO(nr): create/rollback shipping info fixture
     *
     * @test
     */
    public function getById()
    {
        $addressId = 23;
        $info = 'info foo';

        /** @var  OrderShippingInfo $orderShippingInfo */
        $orderShippingInfo = $this->objectManager->create(OrderShippingInfo::class, ['data' => [
            'address_id' => $addressId,
            'info' => $info
        ]]);

        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock = $this->getMock(OrderShippingInfoFactory::class, ['create'], [], '', false);
        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($orderShippingInfo);

        /** @var OrderShippingInfoRepository $repository */
        $repository = $this->objectManager->create(OrderShippingInfoRepository::class, [
            'shippingInfoFactory' => $factoryMock,
            'shippingInfoResource' => $resourceMock
        ]);

        $result = $repository->getById($addressId);
        $this->assertEquals($info, $result->getInfo());
    }

    /**
     * TODO(nr): create/rollback shipping info fixture
     *
     * @test
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Shipment with id "23" does not exist.
     */
    public function getByIdExceptionCase()
    {
        $info = 'info foo';

        /** @var  OrderShippingInfo $orderShippingInfo */
        $orderShippingInfo = $this->objectManager->create(OrderShippingInfo::class, ['data' => [
            'info' => $info,
        ]]);

        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(OrderShippingInfoFactory::class, ['create'], [], '', false);
        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($orderShippingInfo);

        /** @var OrderShippingInfoRepository $repository */
        $repository = $this->objectManager->create(OrderShippingInfoRepository::class, [
            'shippingInfoFactory' => $factoryMock,
            'shippingInfoResource' => $resourceMock
        ]);

        $repository->getById(23);
    }
}
