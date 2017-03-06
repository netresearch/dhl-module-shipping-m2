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

use Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfo;
use Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use \Magento\TestFramework\ObjectManager;
use \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo as ShippingInfoResource;
use \Magento\Framework\Filesystem\Driver\File as Filesystem;

/**
 * ConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class QuoteShippingInfoRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /** @var  QuoteShippingInfoRepository */
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
        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);
        $quoteShippingInfo->setAddressId(23);
        $quoteShippingInfo->setInfo('info');
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class, ['create'], [], '', false);

        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($quoteShippingInfo);

        $this->objectManager->addSharedInstance($factoryMock, \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);

        $this->model  = $this->objectManager->create(QuoteShippingInfoRepository::class);

        $result = $this->model->getById(23);
        $this->assertEquals('info', $result->getInfo());
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);
    }

    /**
     * @test
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Shipment with id "23" does not exist.
     */
    public function getByIdExceptionCase()
    {
        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);
        $quoteShippingInfo->setAddressId(null);
        $quoteShippingInfo->setInfo('info');
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class, ['create'], [], '', false);

        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($quoteShippingInfo);

        $this->objectManager->addSharedInstance($factoryMock, \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);
        $this->model  = $this->objectManager->create(QuoteShippingInfoRepository::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);

        $result = $this->model->getById(23);


    }

    /**
     * @test
     */
    public function testSaveAndDelete()
    {
        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);
        $quoteShippingInfo->setAddressId(12);
        $quoteShippingInfo->setInfo('info');
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['save', 'deleteById', 'load', 'delete'], [], '', false);
        $factoryMock  = $this->getMock(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class, ['create'], [], '', false);
        $entityManagerMock = $this->getMock(\Magento\Framework\EntityManager\EntityManager::class, ['delete'], [], '', false);

        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('save')
            ->willReturn($quoteShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('delete')
            ->willReturn($quoteShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($quoteShippingInfo);


        $entityManagerMock
            ->expects($this->any())
            ->method('delete')
            ->willReturn($quoteShippingInfo);

        $resourceMock
            ->expects($this->any())
            ->method('deleteById')
            ->willReturn(true);

        $this->objectManager->addSharedInstance($factoryMock, \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);
        $this->objectManager->addSharedInstance($entityManagerMock, \Magento\Framework\EntityManager\EntityManager::class);
        $this->model  = $this->objectManager->create(QuoteShippingInfoRepository::class);

        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);
        $this->objectManager->removeSharedInstance(\Magento\Framework\EntityManager\EntityManager::class);


        $this->assertEquals($quoteShippingInfo, $this->model->save($quoteShippingInfo));
        $this->assertTrue($this->model->deleteById(12));

    }

    /**
     * @test
     */
    public function testSaveThrowsException()
    {
        $message = 'Foo.';
        $this->setExpectedException(\Magento\Framework\Exception\CouldNotSaveException::class, $message);

        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['save'], [], '', false);
        $resourceMock
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new \Exception($message));

        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);
        $this->model = $this->objectManager->create(QuoteShippingInfoRepository::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);
        $this->model->save($quoteShippingInfo);

    }


    /**
     * @test
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not delete.
     */
    public function testDeleteThrowsException()
    {
        $message = 'Foo.';
        $this->setExpectedException(\Magento\Framework\Exception\CouldNotDeleteException::class, $message);

        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['delete'], [], '', false);
        $resourceMock
            ->expects($this->once())
            ->method('delete')
            ->willThrowException(new \Exception($message));

        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);
        $this->model = $this->objectManager->create(QuoteShippingInfoRepository::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);
        $this->model->delete($quoteShippingInfo);
    }


    /**
     * @return string[]
     */
    public function getInfoDataProvider()
    {
        $driver = new Filesystem();
        return [
            'response_1' => [
                $driver->fileGetContents(__DIR__ . '/../../_files/getInfoData.json')
            ]
        ];
    }

    /**
     * @test
     * @dataProvider getInfoDataProvider
     * @param string $json
     */
    public function getInfoData($json)
    {

        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);
        $quoteShippingInfo->setAddressId(23);
        $quoteShippingInfo->setInfo($json);
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class, ['create'], [], '', false);

        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($quoteShippingInfo);

        $this->objectManager->addSharedInstance($factoryMock, \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);

        $this->model  = $this->objectManager->create(QuoteShippingInfoRepository::class);

        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);

        $result = $this->model->getInfoData(23);
        $receiver = $result->getReceiver();
        $services = $result->getServices();
        $this->assertTrue($receiver instanceof \Dhl\Shipping\Webservice\ShippingInfo\Receiver);
        $this->assertTrue($services instanceof \Dhl\Shipping\Webservice\ShippingInfo\Services);

    }

    /**
     * @test
     * @dataProvider getInfoDataProvider
     * @param string $json
     */
    public function getInfoDataExceptionCase($json)
    {

        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);
//        $quoteShippingInfo->setInfo($json);
        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class, ['create'], [], '', false);

        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        $resourceMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($quoteShippingInfo);

        $this->objectManager->addSharedInstance($factoryMock, \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->addSharedInstance($resourceMock, \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);

        $this->model  = $this->objectManager->create(QuoteShippingInfoRepository::class);

        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory::class);
        $this->objectManager->removeSharedInstance(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo::class);

        $result = $this->model->getInfoData(23);

        $this->assertNull($result);

    }
}
