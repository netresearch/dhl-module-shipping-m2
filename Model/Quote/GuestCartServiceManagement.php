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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Quote;

use Dhl\Shipping\Api\Data\ServiceInformationInterface;
use Dhl\Shipping\Api\Quote\GuestCartServiceManagementInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Manage Checkout Services. Only delegates to CartServiceManagement class.
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class GuestCartServiceManagement implements GuestCartServiceManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CartServiceManagement
     */
    private $cartServiceManagement;

    /**
     * GuestCartServiceManagement constructor.
     *
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param CartServiceManagement $cartServiceMngmt
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CartServiceManagement $cartServiceMngmt
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartServiceManagement = $cartServiceMngmt;
    }

    /**
     * @param string $cartId
     * @param string $countryId
     * @param string $shippingMethod
     * @return ServiceInformationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getServices($cartId, $countryId, $shippingMethod)
    {
        return $this->cartServiceManagement->getServices(
            $this->getQuoteId($cartId),
            $countryId,
            $shippingMethod
        );
    }

    /**
     * @param string $cartId
     * @param \Magento\Framework\Api\AttributeInterface[] $serviceSelection
     * @param string $shippingMethod
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save($cartId, $serviceSelection, $shippingMethod)
    {
        $this->cartServiceManagement->save($this->getQuoteId($cartId), $serviceSelection, $shippingMethod);
    }

    /**
     * @param $cartId
     * @return int
     */
    private function getQuoteId($cartId)
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $quoteIdMask->getData('quote_id');
    }
}
