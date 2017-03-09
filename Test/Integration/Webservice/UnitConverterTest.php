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
namespace Dhl\Shipping\Webservice;

use \Dhl\Shipping\Api\Webservice\UnitConverterInterface;
use \Magento\TestFramework\ObjectManager;
use \Magento\Directory\Model\CurrencyFactory;

/**
 * UnitConverterTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UnitConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $dataHelper;

    /**
     * prepare object manager, add mocks
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @test
     */
    public function convertDimension()
    {
        $valueInKm = 1;
        $valueInM = 1000;
        $valueInInches = 39370.079;

        /** @var UnitConverterInterface $unitConverter */
        $unitConverter = $this->objectManager->create(UnitConverter::class);

        $conversionResult = $unitConverter->convertDimension(
            $valueInKm,
            \Zend_Measure_Length::KILOMETER,
            \Zend_Measure_Length::METER
        );
        $this->assertEquals($valueInM, $conversionResult);

        $conversionResult = $unitConverter->convertDimension(
            $valueInKm,
            \Zend_Measure_Length::KILOMETER,
            \Zend_Measure_Length::INCH
        );
        $this->assertEquals($valueInInches, $conversionResult);
    }

    /**
     * @test
     */
    public function convertMoney()
    {
        $valueInEur = 1;
        $valueInUsd = 1.066;
        $valueInGbp = 0.853;

        $rateUsdToEur = 0.9377;
        $rateGbpToEur = 1.1723;
        $rateGbpToUsd = 1.2494;

        $currencyMock = $this->getMock(\Magento\Directory\Model\Currency::class, ['getRate'], [], '', false);
        $currencyMock
            ->expects($this->any())
            ->method('getRate')
            ->willReturnOnConsecutiveCalls($rateUsdToEur, $rateGbpToEur, $rateGbpToUsd);

        $currencyFactoryMock = $this->getMock(CurrencyFactory::class, ['create'], [], '', false);
        $currencyFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($currencyMock);

        $dataHelper = $this->objectManager->create(\Magento\Directory\Helper\Data::class, [
            'currencyFactory' => $currencyFactoryMock,
        ]);

        /** @var UnitConverterInterface $unitConverter */
        $unitConverter = $this->objectManager->create(UnitConverter::class, [
            'currencyConverter' => $dataHelper,
        ]);

        $conversionResult = $unitConverter->convertMonetaryValue($valueInUsd, 'USD', 'EUR');
        $this->assertEquals($valueInEur, $conversionResult);

        $conversionResult = $unitConverter->convertMonetaryValue($valueInGbp, 'GBP', 'EUR');
        $this->assertEquals($valueInEur, $conversionResult);

        $conversionResult = $unitConverter->convertMonetaryValue($valueInGbp, 'GBP', 'USD');
        $this->assertEquals($valueInUsd, $conversionResult);
    }

    /**
     * @test
     */
    public function convertWeight()
    {
        $valueInKg = 1;
        $valueInG = 1000;
        $valueInLbs = 2.205;

        /** @var UnitConverterInterface $unitConverter */
        $unitConverter = $this->objectManager->create(UnitConverter::class);

        $conversionResult = $unitConverter->convertWeight(
            $valueInKg,
            \Zend_Measure_Weight::KILOGRAM,
            \Zend_Measure_Weight::GRAM
        );
        $this->assertEquals($valueInG, $conversionResult);

        $conversionResult = $unitConverter->convertWeight(
            $valueInKg,
            \Zend_Measure_Weight::KILOGRAM,
            \Zend_Measure_Weight::LBS
        );
        $this->assertEquals($valueInLbs, $conversionResult);
    }
}
