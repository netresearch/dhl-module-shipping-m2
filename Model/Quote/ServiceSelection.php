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
namespace Dhl\Shipping\Model\Quote;

use Dhl\Shipping\Api\Data\ServiceSelectionInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * ShippingInfo
 *
 * @package  Dhl\Shipping\Model
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceSelection extends AbstractModel implements ServiceSelectionInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Dhl\Shipping\Model\ResourceModel\Quote\Address\ServiceSelection::class);
    }

    /**
     * @return string
     */
    public function getParentId(): string
    {
        return $this->getData('parent_id');
    }

    /**
     * @return String
     */
    public function getServiceCode(): string
    {
        return $this->getData('service_code');
    }

    /**
     * @return string[]
     */
    public function getServiceValue(): array
    {
        return $this->getData('service_value');
    }
}
