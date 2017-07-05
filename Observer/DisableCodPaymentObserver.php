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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Util\ShippingProductsInterface;
use Dhl\Shipping\Util\ShippingProducts;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Session\SessionManagerInterface;

/**
 * DisableCodPaymentObserver
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * DisableCodPaymentObserver constructor.
     *
     * @param ModuleConfigInterface $config
     * @param SessionManagerInterface $checkoutSession
     * @param ShippingProductsInterface $shippingProducts
     */
    public function __construct(
        ModuleConfigInterface $config,
        SessionManagerInterface $checkoutSession,
        ShippingProductsInterface $shippingProducts
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->shippingProducts = $shippingProducts;
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

        /** @var \Magento\Payment\Model\Method\AbstractMethod $methodInstance */
        $methodInstance = $observer->getEvent()->getData('method_instance');

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $recipientCountry = $quote->getShippingAddress()->getCountryId();
        $paymentMethod  = $methodInstance->getCode();

        if (!$this->config->canProcessShipping($shippingMethod, $recipientCountry, $quote->getStoreId())) {
            // shipping with dhl not applicable
            return;
        }

        if (!$this->config->isCodPaymentMethod($paymentMethod, $quote->getStoreId())) {
            // no cod payment method
            return;
        }

        // obtain possible dhl products (national, weltpaket, …) and check if COD is allowed
        $shipperCountry = $this->config->getShipperCountry($quote->getStoreId());
        $euCountries = $this->config->getEuCountryList();

        // find all applicable product codes for the current route
        $routeProductCodes = $this->shippingProducts->getApplicableCodes(
            $shipperCountry,
            $recipientCountry,
            $euCountries
        );
        // check if there are product codes that support COD for the current route
        $codProductCodes = array_intersect($routeProductCodes, [
            ShippingProducts::CODE_PAKET_NATIONAL,
            ShippingProducts::CODE_PAKET_AUSTRIA,
            ShippingProducts::CODE_PAKET_CONNECT,
        ]);

        $canShipWithCod = !empty($codProductCodes);
        $checkResult->setData('is_available', $canShipWithCod);
    }
}
