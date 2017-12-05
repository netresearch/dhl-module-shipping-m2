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

/**
 * DHL Shipping Order Address Extension
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class OrderAddressExtension extends AbstractAddressExtension implements OrderAddressExtensionInterface
{
    protected $_cacheTag = 'dhlshipping_order_info';
    protected $_eventPrefix = 'dhlshipping_order_info';

    /**
     * Init resource model.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Dhl\Shipping\Model\ResourceModel\ShippingInfo\OrderAddressExtension::class);
    }
}
