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
 * @package   Dhl\Shipping
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\ShippingInfo;

use Dhl\Shipping\Api\Data\OrderAddressExtensionInterface;
use Dhl\Shipping\Api\Data\ShippingInfoInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * DHL Shipping Order Address Extension
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
abstract class AbstractAddressExtension extends AbstractModel
{
    const ADDRESS_ID = 'address_id';
    const INFO = 'info';
    const SHIPPING_INFO = 'shipping_info';

    /**
     * @return int
     */
    public function getAddressId()
    {
        return (int)$this->getData(self::ADDRESS_ID);
    }

    /**
     * @param int $addressId
     *
     * @return void
     */
    public function setAddressId($addressId)
    {
        $this->setData(self::ADDRESS_ID, $addressId);
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return $this->getData(self::INFO);
    }

    /**
     * @param string $info
     *
     * @return void
     */
    public function setInfo($info)
    {
        $this->setData(self::INFO, $info);
    }

    /**
     * @return ShippingInfoInterface
     */
    public function getShippingInfo()
    {
        return $this->getData(self::SHIPPING_INFO);
    }

    /**
     * @param ShippingInfoInterface $info
     *
     * @return void
     */
    public function setShippingInfo(ShippingInfoInterface $info)
    {
        $this->setData(self::SHIPPING_INFO, $info);
    }
}
