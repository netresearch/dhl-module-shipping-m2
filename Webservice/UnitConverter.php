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
 * @package   Dhl\Shipping\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Webservice;

/**
 * UnitConverter
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UnitConverter implements UnitConverterInterface
{
    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $currencyConverter;

    /**
     * @var \Magento\Shipping\Helper\Carrier
     */
    private $unitConverter;

    /**
     * UnitConverter constructor.
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Directory\Helper\Data $currencyConverter
     * @param \Magento\Shipping\Helper\Carrier $unitConverter
     */
    public function __construct(
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Directory\Helper\Data $currencyConverter,
        \Magento\Shipping\Helper\Carrier $unitConverter
    ) {
        $this->localeFormat = $localeFormat;
        $this->currencyConverter = $currencyConverter;
        $this->unitConverter = $unitConverter;
    }

    /**
     * @param float $value
     * @param string $unitIn
     * @param string $unitOut
     * @return float|null
     */
    public function convertDimension($value, $unitIn, $unitOut)
    {
        $value = $this->localeFormat->getNumber($value);
        $converted = $this->unitConverter->convertMeasureDimension($value, $unitIn, $unitOut);
        if (!$converted) {
            return null;
        }

        return round($converted, self::CONVERSION_PRECISION);
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
        $value = (float) $this->localeFormat->getNumber($value);

        if ($value === 0.0) {
            return $value;
        }

        $converted = $this->unitConverter->convertMeasureWeight($value, $unitIn, $unitOut);
        if (!$converted) {
            return null;
        }

        return round($converted, self::CONVERSION_PRECISION);
    }
}
