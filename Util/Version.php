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
 * @package   Dhl\Shipping\Util
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Util;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Detect application and module versions.
 *
 * @category Dhl
 * @package  Dhl\Shipping\Util
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Version
{
    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Version constructor.
     * @param ModuleConfigInterface $moduleConfig
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ModuleConfigInterface $moduleConfig,
        ProductMetadataInterface $productMetadata
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Detect module version.
     * Uses version string stored in config.
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return $this->moduleConfig->getModuleVersion();
    }

    /**
     * Detect application version.
     *
     * @return string
     */
    public function getProductVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @param string $format
     * @return string
     */
    public function getFullVersion($format)
    {
        $productVersion = $this->getProductVersion();
        $moduleVersion = $this->getModuleVersion();

        return sprintf($format, $productVersion, $moduleVersion);
    }
}
