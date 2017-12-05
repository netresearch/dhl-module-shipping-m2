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
namespace Dhl\Shipping\Plugin\Checkout;

//use Dhl\Shipping\Api\Data\QuoteAddressExtensionInterfaceFactory;
use Dhl\Shipping\Api\QuoteAddressExtensionRepositoryInterface;
use Dhl\Shipping\Model\ShippingInfo\AbstractAddressExtension;
use Dhl\Shipping\Model\ShippingInfoBuilder;
use Dhl\Shipping\Model\ShippingInfo\QuoteAddressExtensionFactory;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * ShippingInformationManagementPlugin
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingInformationManagementPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteAddressExtensionRepositoryInterface
     */
    private $addressExtensionRepository;

    /**
     * @var QuoteAddressExtensionFactory
     */
    private $addressExtensionFactory;

    /**
     * @var ShippingInfoBuilder
     */
    private $shippingInfoBuilder;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteAddressExtensionRepositoryInterface $addressExtensionRepository
     * @param QuoteAddressExtensionFactory $addressExtensionFactory
     * @param ShippingInfoBuilder $shippingInfoBuilder
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        QuoteAddressExtensionRepositoryInterface $addressExtensionRepository,
        QuoteAddressExtensionFactory $addressExtensionFactory,
        ShippingInfoBuilder $shippingInfoBuilder
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->addressExtensionRepository = $addressExtensionRepository;
        $this->addressExtensionFactory = $addressExtensionFactory;
        $this->shippingInfoBuilder = $shippingInfoBuilder;
    }

    /**
     * Will be called, the moment, the shipping address is saved
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement   $subject
     * @param int                                                     $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     *
     * @return array|null
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $addressInformation->getShippingAddress();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        $this->shippingInfoBuilder->setShippingAddress($shippingAddress);
        $shippingInfo = $this->shippingInfoBuilder->create();

        $addressExtension = $this->addressExtensionFactory->create(['data' => [
            AbstractAddressExtension::ADDRESS_ID => $quote->getShippingAddress()->getId(),
            AbstractAddressExtension::SHIPPING_INFO => $shippingInfo,
        ]]);

        $this->addressExtensionRepository->save($addressExtension);

        return null;
    }
}
