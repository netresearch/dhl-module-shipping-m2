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

use Dhl\Shipping\Api\Data\QuoteAddressExtensionInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Additional quote address attributes, primarily shipping info data structure.
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface QuoteAddressExtensionRepositoryInterface extends AddressExtensionRepositoryInterface
{
    /**
     * Retrieve Shipping Address Extension by Quote Address ID.
     *
     * @param int $addressId Shipping Address ID
     * @return QuoteAddressExtensionInterface
     */
    public function getById($addressId);

    /**
     * Save Shipping Address Extension. PK equals Quote Address ID.
     *
     * @param QuoteAddressExtensionInterface $addressExtension
     * @return QuoteAddressExtensionInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteAddressExtensionInterface $addressExtension);

    /**
     * Delete Shipping Address Extension
     *
     * @param QuoteAddressExtensionInterface $entity
     * @return bool
     */
    public function delete(QuoteAddressExtensionInterface $entity);

    /**
     * Delete Shipping Address Extension using Quote Address ID.
     *
     * @param int $addressId
     * @return bool
     */
    public function deleteById($addressId);
}
