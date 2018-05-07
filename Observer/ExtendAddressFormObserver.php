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
namespace Dhl\Shipping\Observer;

use \Dhl\Shipping\Api\OrderAddressExtensionRepositoryInterface;
use \Dhl\Shipping\Block\Adminhtml\Order\Shipping\Address\Form;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Framework\Registry;
use \Magento\Sales\Block\Adminhtml\Order\Address;

/**
 * ExtendAddressFormObserver
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ExtendAddressFormObserver implements ObserverInterface
{
    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var OrderAddressExtensionRepositoryInterface
     */
    private $addressExtensionRepository;

    /**
     * ExtendAddressFormObserver constructor.
     *
     * @param Registry $coreRegistry
     * @param OrderAddressExtensionRepositoryInterface $addressExtensionRepository
     */
    public function __construct(
        Registry $coreRegistry,
        OrderAddressExtensionRepositoryInterface $addressExtensionRepository
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->addressExtensionRepository = $addressExtensionRepository;
    }

    /**
     * When the shipping address edit page in the backend is loaded, add the shipping address data into the form
     *
     * Event:
     * - adminhtml_block_html_before
     *
     * @param Observer $observer
     *
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var Address $container */
        $container = $observer->getEvent()->getData('block');
        if (!$container instanceof Address) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Address $address */
        $address = $this->coreRegistry->registry('order_address');
        if (!$address || ($address->getAddressType() !== \Magento\Sales\Model\Order\Address::TYPE_SHIPPING)) {
            return;
        }

        $shippingMethod = $address->getOrder()->getShippingMethod(true);
        if ($shippingMethod->getData('carrier_code') !== \Dhl\Shipping\Model\Shipping\Carrier::CODE) {
            return;
        }

        try {
            // load previous info data
            $this->addressExtensionRepository->getShippingInfo($address->getEntityId());
        } catch (NoSuchEntityException $e) {
            return;
        }

        $origAddressForm = $container->getChildBlock('form');
        if (!$origAddressForm instanceof \Magento\Sales\Block\Adminhtml\Order\Create\Form\Address) {
            return;
        }

        /** @var Form $dhlAddressForm */
        $dhlAddressForm = $container->getLayout()->getBlock('shipping_sales_order_address_form');
        $dhlAddressForm->setDisplayVatValidationButton($origAddressForm->getDisplayVatValidationButton());
        $container->setChild('form', $dhlAddressForm);
    }
}
