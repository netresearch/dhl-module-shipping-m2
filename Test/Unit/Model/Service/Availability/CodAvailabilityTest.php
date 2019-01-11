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
 * @package   Dhl\Shipping\Test\Unit
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2019 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Test\Unit\Model\Service\Availability;

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterfaceFactory;
use Dhl\Shipping\Api\ServicePoolInterface;
use Dhl\Shipping\Api\ServiceSelectionRepositoryInterface;
use Dhl\Shipping\Model\Config\ConfigAccessorInterface;
use Dhl\Shipping\Model\Config\ModuleConfig;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Quote\ServiceSelection;
use Dhl\Shipping\Model\ResourceModel\Quote\Address\ServiceSelectionCollection;
use Dhl\Shipping\Model\Service\Availability\CodAvailability;
use Dhl\Shipping\Model\Service\ServiceCollection;
use Dhl\Shipping\Model\Service\ServiceConfig;
use Dhl\Shipping\Service\ServiceSettings;
use Dhl\Shipping\Util\ShippingRoutes\RouteValidatorInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CodAvailabilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ModuleConfigInterface|MockObject
     */
    private $config;

    /**
     * @var ServiceConfig|MockObject
     */
    private $serviceConfig;

    /**
     * @var ServicePoolInterface|MockObject
     */
    private $servicePool;

    /**
     * @var RouteValidatorInterface|MockObject
     */
    private $routeValidator;

    /**
     * @var ServiceSettingsInterfaceFactory|MockObject
     */
    private $serviceSettingsFactory;

    /**
     * @var ServiceSelectionRepositoryInterface|MockObject
     */
    private $serviceSelectionRepository;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var ServiceSettings|MockObject
     */
    private $serviceSettings;

    /**
     * @var ServiceSelection|MockObject
     */
    private $serviceCollection;

    protected function setUp()
    {
        parent::setUp();

        $configAccessor = $this->getMockBuilder(ConfigAccessorInterface::class)->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->config = $this->getMockBuilder(ModuleConfig::class)->disableOriginalConstructor()->getMock();

        $this->serviceConfig = $this->objectManager->getObject(ServiceConfig::class, [
            'configAccessor' => $configAccessor
        ]);

        $this->servicePool = $this->getMockBuilder(ServicePoolInterface::class)->getMock();

        $this->routeValidator = $this->getMockBuilder(RouteValidatorInterface::class)->getMock();

        $this->serviceSettingsFactory = $this->getMockBuilder(ServiceSettingsInterfaceFactory::class)
                                             ->disableOriginalConstructor()
                                             ->getMock();

        $this->serviceSelectionRepository = $this->getMockBuilder(ServiceSelectionRepositoryInterface::class)
                                                 ->getMock();
        $this->quote = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->getMock();

        $this->serviceSettings = $this->getMockBuilder(ServiceSettings::class)
                                      ->disableOriginalConstructor()
                                      ->getMock();
        $this->serviceCollection = $this->getMockBuilder(ServiceCollection::class)->getMock();
    }

    /**
     * @test
     */
    public function codIsAvailbale()
    {
        $shipperCountry = 'AT';
        $recipientCountry = 'DE';
        $euCountryList = ['DE', 'AT', 'PL'];
        $shippingAddress = new DataObject(
            [
                'shipping_method' => 'flatrate_flatrate',
                'country_id' => $recipientCountry,
            ]
        );

        $this->quote->expects($this->once())->method('getShippingAddress')->willReturn($shippingAddress);

        /** @var ServiceSelection|MockObject $serviceSelection */
        $serviceSelection = $this->getMockBuilder(ServiceSelection::class)
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->serviceCollection->method('filter')->willReturn($this->serviceCollection);
        $this->serviceCollection->method('count')->willReturn(1);

        $quoteServiceCollection = $this->getMockBuilder(ServiceSelectionCollection::class)
                                       ->disableOriginalConstructor()
                                       ->getMock();
        $quoteServiceCollection->method('getItems')->willReturn([$serviceSelection]);

        $this->serviceSettingsFactory->expects($this->once())->method('create')->willReturn($this->serviceSettings);

        $this->servicePool->expects($this->once())->method('getServices')->willReturn($this->serviceCollection);

        $this->serviceSelectionRepository->expects($this->once())
                                         ->method('getByQuoteAddressId')
                                         ->willReturn($quoteServiceCollection);

        $this->config
            ->expects($this->once())
            ->method('getShipperCountry')
            ->willReturn($shipperCountry);

        $this->config
            ->expects($this->once())
            ->method('getEuCountryList')
            ->willReturn($euCountryList);

        /** @var CodAvailability $codAvailability */
        $codAvailability = $this->objectManager->getObject(CodAvailability::class, [
            'config' => $this->config,
            'serviceConfig' => $this->serviceConfig,
            'servicePool' => $this->servicePool,
            'routeValidator' => $this->routeValidator,
            'serviceSettingsFactory' => $this->serviceSettingsFactory,
            'serviceSelectionRepository' => $this->serviceSelectionRepository
        ]);

        $res = $codAvailability->isCodAvailable($this->quote, $recipientCountry);

        $this->assertTrue($res);
    }

    /**
     * @test
     */
    public function isNotCodAvailable()
    {
        $shipperCountry = 'AT';
        $recipientCountry = 'PL';
        $euCountryList = ['DE', 'AT', 'PL'];

        $this->serviceCollection->method('filter')->willReturn($this->serviceCollection);
        $this->serviceCollection->method('count')->willReturn(0);

        $this->servicePool->expects($this->once())->method('getServices')->willReturn($this->serviceCollection);

        $this->config
            ->expects($this->once())
            ->method('getShipperCountry')
            ->willReturn($shipperCountry);

        $this->config
            ->expects($this->once())
            ->method('getEuCountryList')
            ->willReturn($euCountryList);

        /** @var CodAvailability $codAvailability */
        $codAvailability = $this->objectManager->getObject(CodAvailability::class, [
            'config' => $this->config,
            'serviceConfig' => $this->serviceConfig,
            'servicePool' => $this->servicePool,
            'routeValidator' => $this->routeValidator,
            'serviceSettingsFactory' => $this->serviceSettingsFactory,
            'serviceSelectionRepository' => $this->serviceSelectionRepository
        ]);

        $res = $codAvailability->isCodAvailable($this->quote, $recipientCountry);

        $this->assertFalse($res);
    }
}
