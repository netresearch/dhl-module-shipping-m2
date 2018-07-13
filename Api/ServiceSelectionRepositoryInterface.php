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
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Api;

use Dhl\Shipping\Api\Data\ServiceSelectionInterface;
use Dhl\Shipping\Model\ResourceModel\Order\Address\ServiceSelectionCollection as OrderServiceSelectionCollection;
use Dhl\Shipping\Model\ResourceModel\Quote\Address\ServiceSelectionCollection as QuoteServiceSelectionCollection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository for DHL Shipping Service Selections stored with either the Quote oder Order Address
 *
 * @package  Dhl\Shipping\Api
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface ServiceSelectionRepositoryInterface
{
    /**
     * @param ServiceSelectionInterface $serviceSelection
     * @return ServiceSelectionInterface
     * @throws CouldNotSaveException
     */
    public function save(ServiceSelectionInterface $serviceSelection): ServiceSelectionInterface;

    /**
     * @param string $addressId
     * @return QuoteServiceSelectionCollection
     * @throws NoSuchEntityException
     */
    public function getByQuoteAddressId($addressId): QuoteServiceSelectionCollection;

    /**
     * @param string $addressId
     * @return OrderServiceSelectionCollection
     * @throws NoSuchEntityException
     */
    public function getByOrderAddressId($addressId): OrderServiceSelectionCollection;

    /**
     * @param ServiceSelectionInterface $serviceSelection
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ServiceSelectionInterface $serviceSelection): bool;

    /**
     * @param string $addressId
     * @throws CouldNotDeleteException
     */
    public function deleteByQuoteAddressId($addressId);

    /**
     * @param string $addressId
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteByOrderAddressId($addressId);
}
