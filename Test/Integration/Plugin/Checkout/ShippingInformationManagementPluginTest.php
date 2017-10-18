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
namespace Dhl\Shipping\Plugin;

use \Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfoRepository;
use \Magento\Checkout\Model\ShippingInformation;
use \Magento\Checkout\Model\ShippingInformationManagement;
use \Magento\Quote\Model\Quote;
use \Magento\Quote\Model\QuoteRepository;
use \Magento\Quote\Model\Quote\Address as ShippingAddress;
use \Magento\TestFramework\Interception\PluginList;
use \Magento\TestFramework\ObjectManager;

/**
 * ShippingInformationManagementPluginTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingInformationManagementPluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var ShippingAddress|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shippingAddress;

    /**
     * @var QuoteShippingInfoRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $infoRepository;

    /**
     * @var ShippingInformationManagement
     */
    private $shippingInfoManagement;

    public function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();

        $this->shippingAddress = $this->getMockBuilder(ShippingAddress::class)
            ->setMethods(['getShippingRateByCode', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->shippingAddress
            ->expects($this->once())
            ->method('getShippingRateByCode')
            ->willReturn('foo_bar');
        $this->shippingAddress->setData([
            'id' => 808,
            'country_id' => 'XX',
        ]);

        /** @var Quote|\PHPUnit_Framework_MockObject_MockObject $quote */
        $quote = $this->getMockBuilder(Quote::class)
            ->setMethods(['getShippingAddress'])
            ->disableOriginalConstructor()
            ->getMock();
        $quote->setItemsCount(1);
        $quote->setStoreId(1);
        $quote->expects($this->any())->method('getShippingAddress')->willReturn($this->shippingAddress);

        $quoteRepository = $this->getMockBuilder(QuoteRepository::class)
            ->setMethods(['getActive', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $quoteRepository
            ->expects($this->any())
            ->method('getActive')
            ->willReturn($quote);
        $this->objectManager->addSharedInstance($quoteRepository, QuoteRepository::class);

        $this->infoRepository = $this->getMockBuilder(QuoteShippingInfoRepository::class)
            ->setMethods(['save'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->addSharedInstance($this->infoRepository, QuoteShippingInfoRepository::class);

        $paymentManagement = $this->getMockBuilder(\Magento\Quote\Model\PaymentMethodManagement::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMock();
        $cartTotalsRepository = $this->getMockBuilder(\Magento\Quote\Model\Cart\CartTotalRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        /** @var ShippingInformationManagement $subject */
        $this->shippingInfoManagement = $this->objectManager->create(ShippingInformationManagement::class, [
            'paymentMethodManagement' => $paymentManagement,
            'cartTotalsRepository' => $cartTotalsRepository,
        ]);
    }

    protected function tearDown()
    {
        $this->objectManager->removeSharedInstance(QuoteRepository::class);
        $this->objectManager->removeSharedInstance(QuoteShippingInfoRepository::class);
        $this->objectManager->removeSharedInstance(PluginList::class);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function quoteInformationIsSaved()
    {
        $cartId = 303;
        $carrierCode = 'foo';
        $methodCode = 'bar';

        $shippingInformation = $this->objectManager->create(ShippingInformation::class, ['data' => [
            'shipping_address' => $this->shippingAddress,
            'carrier_code' => $carrierCode,
            'method_code' => $methodCode,
        ]]);

        $this->infoRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(\Dhl\Shipping\Model\ShippingInfo\QuoteShippingInfo::class));

        $this->shippingInfoManagement->saveAddressInformation($cartId, $shippingInformation);
    }
}
