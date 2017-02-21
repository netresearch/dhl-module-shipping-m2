<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Webservice;

use \Dhl\Versenden\Api\Webservice\UnitConverterInterface;

/**
 * UnitConverter
 *
 * @category Dhl
 * @package  Dhl\Versenden\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UnitConverter implements UnitConverterInterface
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $currencyConverter;

    /**
     * UnitConverter constructor.
     * @param \Magento\Directory\Helper\Data $currencyConverter
     */
    public function __construct(\Magento\Directory\Helper\Data $currencyConverter)
    {
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @param float $value
     * @param string $unitIn
     * @param string $unitOut
     * @return float|null
     */
    public function convertDimension($value, $unitIn, $unitOut)
    {
        if (!is_numeric($value)) {
            return null;
        }

        $dimensionConverter = new \Zend_Measure_Length($value, $unitIn);
        $dimensionConverter->setType($unitOut);

        return $dimensionConverter->getValue(self::CONVERSION_PRECISION);
    }

    /**
     * @param float $value
     * @param string $unitIn
     * @param string $unitOut
     * @return float|null
     */
    public function convertMonetaryValue($value, $unitIn, $unitOut)
    {
        if (!is_numeric($value)) {
            return null;
        }

        $amount = $this->currencyConverter->currencyConvert($value, $unitIn, $unitOut);
        return round($amount, self::CONVERSION_PRECISION);
    }

    /**
     * @param float $value
     * @param string $unitIn
     * @param string $unitOut
     * @return float|null
     */
    public function convertWeight($value, $unitIn, $unitOut)
    {
        if (!is_numeric($value)) {
            return null;
        }

        $weightConverter = new \Zend_Measure_Weight($value, $unitIn);
        $weightConverter->setType($unitOut);

        return $weightConverter->getValue(self::CONVERSION_PRECISION);
    }
}
