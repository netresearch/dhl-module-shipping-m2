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

use \Magento\Framework\App\ProductMetadataInterface;
use \Magento\Framework\Module\ModuleListInterface;


/**
 * Version
 *
 * @category Dhl
 * @package  Dhl\Shipping\Util
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Version
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * Version constructor.
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

    /**
     * @todo(nr): read version from module.xml
     * @see \Magento\Framework\Component\ComponentRegistrarInterface::getPath
     * @see \Magento\Framework\Module\ModuleList\Loader::load
     *   or, if it is the same
     * @link https://magento.stackexchange.com/a/99535
     *
     * @return string
     */
    public function getModuleVersion()
    {
        return '?';
    }

    /**
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
