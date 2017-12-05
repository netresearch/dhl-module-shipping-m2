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
namespace Dhl\Shipping\Api;

use Dhl\Shipping\Api\Data\OrderAddressExtensionInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Additional order address attributes, primarily shipping info data structure.
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface OrderAddressExtensionRepositoryInterface extends AddressExtensionRepositoryInterface
{
    /**
     * Retrieve Shipping Address Extension by Order Address ID.
     *
     * @param int $addressId Shipping Address ID
     * @return OrderAddressExtensionInterface
     */
    public function getById($addressId);

    /**
     * Save Shipping Address Extension. PK equals Order Address ID.
     *
     * @param OrderAddressExtensionInterface $addressExtension
     * @return OrderAddressExtensionInterface
     * @throws CouldNotSaveException
     */
    public function save(OrderAddressExtensionInterface $addressExtension);

    /**
     * Delete Shipping Address Extension
     *
     * @param OrderAddressExtensionInterface $addressExtension
     * @return bool
     */
    public function delete(OrderAddressExtensionInterface $addressExtension);

    /**
     * Delete Shipping Address Extension using Order Address ID.
     *
     * @param int $addressId
     * @return bool
     */
    public function deleteById($addressId);
}
