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
 * @package   Dhl\Shipping\Model
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Service;

use Dhl\Shipping\Api\Data\ServiceInformationInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class ServiceInformation
 *
 * @package Dhl\Shipping\Model
 */
class ServiceInformation extends AbstractModel implements ServiceInformationInterface
{
    /**
     * @return mixed[][]
     */
    public function getServices(): array
    {
        return $this->getData('services');
    }

    /**
     * @return string[][]
     */
    public function getCompatibility(): array
    {
        return $this->getData('compatibility');
    }
}
