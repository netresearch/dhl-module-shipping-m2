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

use \Dhl\Shipping\Model\ResourceModel\ShippingInfo\QuoteShippingInfo as ShippingInfoResource;
use \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfo;
use \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoRepository;
use \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Filesystem\Driver\File as Filesystem;
use \Magento\TestFramework\ObjectManager;

/**
 * RepositoryTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class RepositoryTest extends \PHPUnit_Framework_TestCase
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
     * Assert resource model's save being called on repository's save
     *
     * @test
     */
    public function saveSuccess()
    {
        $addressId = 23;
        $info = 'info foo';

        /** @var  QuoteShippingInfo $orderShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class, ['data' => [
            'address_id' => $addressId,
            'info' => $info
        ]]);

        $resourceMock = $this->getMock(ShippingInfoResource::class, ['save'], [], '', false);
        $resourceMock
            ->expects($this->once())
            ->method('save')
            ->with($quoteShippingInfo);

        /** @var QuoteShippingInfoRepository $repository */
        $repository = $this->objectManager->create(QuoteShippingInfoRepository::class, [
            'shippingInfoResource' => $resourceMock
        ]);

        $repository->save($quoteShippingInfo);
    }

    /**
     * Assert exception being converted and passed through from resource to repository.
     *
     * @test
     */
    public function saveException()
    {
        $message = 'Exception Foo!';
        $this->setExpectedException(CouldNotSaveException::class, $message);

        /** @var  QuoteShippingInfo $orderShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);

        $resourceMock = $this->getMock(ShippingInfoResource::class, ['save'], [], '', false);
        $resourceMock
            ->expects($this->once())
            ->method('save')
            ->with($quoteShippingInfo)
            ->willThrowException(new \Exception($message));

        /** @var QuoteShippingInfoRepository $repository */
        $repository = $this->objectManager->create(QuoteShippingInfoRepository::class, [
            'shippingInfoResource' => $resourceMock
        ]);

        $repository->save($quoteShippingInfo);
    }

    /**
     * Assert resource model's delete method being called when deleting by ID.
     *
     * @test
     */
    public function deleteSuccess()
    {
        $addressId = 23;
        $info = 'info foo';

        /** @var  QuoteShippingInfo $orderShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class, ['data' => [
            'address_id' => $addressId,
            'info' => $info
        ]]);

        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load', 'delete'], [], '', false);
        $resourceMock
            ->expects($this->once())
            ->method('delete')
            ->with($quoteShippingInfo);

        $factoryMock  = $this->getMock(QuoteShippingInfoFactory::class, ['create'], [], '', false);
        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        /** @var QuoteShippingInfoRepository $repository */
        $repository = $this->objectManager->create(QuoteShippingInfoRepository::class, [
            'shippingInfoFactory' => $factoryMock,
            'shippingInfoResource' => $resourceMock,
        ]);

        $isDeleted = $repository->deleteById($addressId);
        $this->assertTrue($isDeleted);
    }

    /**
     * @test
     */
    public function deleteException()
    {
        $message = 'Exception Foo!';
        $this->setExpectedException(CouldNotDeleteException::class, $message);

        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);

        $resourceMock = $this->getMock(ShippingInfoResource::class, ['delete'], [], '', false);
        $resourceMock
            ->expects($this->once())
            ->method('delete')
            ->with($quoteShippingInfo)
            ->willThrowException(new \Exception($message));

        /** @var QuoteShippingInfoRepository $repository */
        $repository = $this->objectManager->create(QuoteShippingInfoRepository::class, [
            'shippingInfoResource' => $resourceMock
        ]);

        $repository->delete($quoteShippingInfo);
    }

    /**
     * @test
     * @dataProvider getInfoDataProvider
     * @param string $json
     */
    public function getInfoDataSuccess($json)
    {
        $addressId = 23;

        /** @var  QuoteShippingInfo $orderShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class, ['data' => [
            'address_id' => $addressId,
            'info' => $json
        ]]);

        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(QuoteShippingInfoFactory::class, ['create'], [], '', false);
        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        /** @var QuoteShippingInfoRepository $repository */
        $repository = $this->objectManager->create(QuoteShippingInfoRepository::class, [
            'shippingInfoFactory' => $factoryMock,
            'shippingInfoResource' => $resourceMock
        ]);

        /** @var \Dhl\Shipping\Webservice\ShippingInfo\Info $result */
        $result = $repository->getInfoData($addressId);
        $this->assertTrue($result->getReceiver() instanceof \Dhl\Shipping\Webservice\ShippingInfo\Receiver);
        $this->assertTrue($result->getServices() instanceof \Dhl\Shipping\Webservice\ShippingInfo\Services);
    }

    /**
     * @test
     */
    public function infoDataNotFound()
    {
        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class);

        $resourceMock = $this->getMock(ShippingInfoResource::class, ['load'], [], '', false);
        $factoryMock  = $this->getMock(QuoteShippingInfoFactory::class, ['create'], [], '', false);
        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        /** @var QuoteShippingInfoRepository $repository */
        $repository = $this->objectManager->create(QuoteShippingInfoRepository::class, [
            'shippingInfoFactory' => $factoryMock,
            'shippingInfoResource' => $resourceMock
        ]);

        $this->assertNull($repository->getInfoData(23));
    }
}
