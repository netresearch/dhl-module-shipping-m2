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

use Magento\Framework\Model\AbstractModel;

/**
 * ShippingInfo
 *
 * @package  Dhl\Shipping\Model
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceSelection extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Dhl\Shipping\Model\ResourceModel\Quote\ServiceSelection::class);
    }


    /**
     * Quote Address ID.
     * @var string
     */
    private $addressId;

    /**
     * @var String
     */
    private $serviceCode;

    /**
     * @var int|string|bool
     */
    private $serviceValue;

    /**
     * @return string
     */
    public function getAddressId(): string
    {
        return $this->addressId;
    }

    /**
     * @return String
     */
    public function getServiceCode(): string
    {
        return $this->serviceCode;
    }

    /**
     * @return bool|int|string
     */
    public function getServiceValue()
    {
        return $this->serviceValue;
    }
}
