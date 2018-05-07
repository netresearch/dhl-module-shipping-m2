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

use Dhl\Shipping\Api\Data\ShippingInfo\ServiceInterface;
use Dhl\Shipping\Api\Quote\GuestCartServiceManagementInterface;
use Dhl\Shipping\Model\Config\ModuleConfig;
use Dhl\Shipping\Model\Service\CheckoutServiceProvider;
use Dhl\Shipping\Model\Service\ServiceCollection;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteRepository;

/**
 * Manage Checkout Services
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class GuestCartServiceManagement implements GuestCartServiceManagementInterface
{
    /**
     * @var CheckoutServiceProvider
     */
    private $checkoutServiceProvider;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * GuestCartCheckoutFieldManagement constructor.
     * @param CheckoutServiceProvider $serviceProvider
     * @param ModuleConfig $moduleConfig
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        CheckoutServiceProvider $serviceProvider,
        ModuleConfig $moduleConfig,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteRepository $quoteRepository
    ) {
        $this->checkoutServiceProvider = $serviceProvider;
        $this->moduleConfig = $moduleConfig;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param int $cartId
     * @param string $countryId
     * @param string $shippingMethod
     * @return ServiceCollection|ServiceInterface[]
     */
    public function getServices($cartId, $countryId, $shippingMethod)
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->quoteRepository->get($quoteIdMask->getQuoteId());
        $dhlShippingMethod = $this->moduleConfig->getShippingMethods($quote->getStoreId());
        $shippingMethod = $shippingMethod . '_' . $shippingMethod;

        if (!in_array($shippingMethod, $dhlShippingMethod)) {
            return [];
        }

        $services = $this->checkoutServiceProvider->getServices($countryId, $quote->getStoreId());

        return $services;
    }
}
