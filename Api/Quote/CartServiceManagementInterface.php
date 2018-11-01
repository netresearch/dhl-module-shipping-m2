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
 * Interface CartServiceManagementInterface
 *
 * Get Checkout Services
 *
 * @api
 * @package  Dhl\Shipping\Api
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface CartServiceManagementInterface
{
    /**
     * @param int $cartId
     * @param string $countryId
     * @param string $shippingMethod
     * @return \Dhl\Shipping\Api\Data\ServiceInformationInterface
     */
    public function getServices($cartId, $countryId, $shippingMethod);

    /**
     * @param string $cartId
     * @param string[] $serviceSelection
     * @param string $shippingMethod
     * @return void
     */
    public function save($cartId, $serviceSelection, $shippingMethod);
}
