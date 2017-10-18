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
use \Magento\TestFramework\ObjectManager;

/**
 * QuoteShippingInfoRepositoryTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class QuoteShippingInfoRepositoryTest extends \PHPUnit\Framework\TestCase
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
     * @test
     */
    public function getById()
    {
        $addressId = 23;
        $info = 'info foo';

        /** @var  QuoteShippingInfo $orderShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class, ['data' => [
            'address_id' => $addressId,
            'info' => $info
        ]]);

        $resourceMock = $this->getMockBuilder(ShippingInfoResource::class)
            ->setMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock = $this->getMockBuilder(QuoteShippingInfoFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        /** @var QuoteShippingInfoRepository $repository */
        $repository = $this->objectManager->create(QuoteShippingInfoRepository::class, [
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
    public function getByIdException()
    {
        $info = 'info foo';

        /** @var  QuoteShippingInfo $quoteShippingInfo */
        $quoteShippingInfo = $this->objectManager->create(QuoteShippingInfo::class, ['data' => [
            'info' => $info,
        ]]);

        $resourceMock = $this->getMockBuilder(ShippingInfoResource::class)
            ->setMethods(['load'])
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock = $this->getMockBuilder(QuoteShippingInfoFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($quoteShippingInfo);

        /** @var QuoteShippingInfoRepository $repository */
        $repository = $this->objectManager->create(QuoteShippingInfoRepository::class, [
            'shippingInfoFactory' => $factoryMock,
            'shippingInfoResource' => $resourceMock
        ]);

        $repository->getById(23);
    }
}
