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
 * @package   Dhl\Shipping\Api
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Api\Quote;

/**
 * Manage value-added services during checkout operations for guest customers.
 *
 * @api
 * @package  Dhl\Shipping\Api
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface GuestCartServiceManagementInterface
{
    /**
     * Obtain available service metadata, including rendering, compatibility, and default value information.
     *
     * @param string $cartId
     * @param string $countryId
     * @param string $shippingMethod
     * @param string $postalCode
     * @return \Dhl\Shipping\Api\Data\ServiceInformationInterface
     */
    public function getServices($cartId, $countryId, $shippingMethod, $postalCode);

    /**
     * Persist the service values as selected by a consumer or merchant.
     *
     * @param string $cartId
     * @param \Magento\Framework\Api\AttributeInterface[] $serviceSelection
     * @param string $shippingMethod
     * @return void
     */
    public function save($cartId, $serviceSelection, $shippingMethod);
}
