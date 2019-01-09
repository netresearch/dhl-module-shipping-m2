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
 * @package   Dhl\Shipping\Observer
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Service\Availability\CodAvailability;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * DisableCodPaymentObserver
 *
 * @package  Dhl\Shipping\Observer
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class DisableCodPaymentObserver implements ObserverInterface
{
    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var SessionManagerInterface|CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CodAvailability
     */
    private $codAvailability;

    /**
     * DisableCodPaymentObserver constructor.
     *
     * @param ModuleConfigInterface $config
     * @param CheckoutSession|SessionManagerInterface $checkoutSession
     * @param CodAvailability $codAvalaibility
     */
    public function __construct(
        ModuleConfigInterface $config,
        SessionManagerInterface $checkoutSession,
        CodAvailability $codAvalaibility
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->codAvailability = $codAvalaibility;
    }

    /**
     * Disable COD payment methods if it is not available for the current
     * shipping product.
     * - event: payment_method_is_active
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $checkResult = $observer->getEvent()->getData('result');
        if (!$checkResult->getData('is_available')) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getData('quote');
        if (!$quote) {
            $quote = $this->checkoutSession->getQuote();
        }
        if (!$quote) {
            return;
        }

        /** @var \Magento\Payment\Model\MethodInterface $methodInstance */
        $methodInstance = $observer->getEvent()->getData('method_instance');

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $recipientCountry = $quote->getShippingAddress()->getCountryId();
        $paymentMethod = $methodInstance->getCode();

        if (!$this->config->canProcessShipping($shippingMethod, $recipientCountry, $quote->getStoreId())) {
            // shipping with dhl not applicable
            return;
        }

        if (!$this->config->isCodPaymentMethod($paymentMethod, $quote->getStoreId())) {
            // no cod payment method
            return;
        }

        $canShipWithCod = $this->codAvailability->isCodAvailable($quote, $recipientCountry);
        $checkResult->setData('is_available', $canShipWithCod);
    }
}
