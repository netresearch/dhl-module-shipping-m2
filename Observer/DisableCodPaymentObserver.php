<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Observer;

use \Dhl\Versenden\Api\Config\ModuleConfigInterface;
use \Dhl\Versenden\Api\Service\Cod;
use \Dhl\Versenden\Api\Service\Filter\ProductFilter;
use \Dhl\Versenden\Api\Service\ServiceCollection;
use \Dhl\Versenden\Api\Service\ServiceCollectionFactory;
use \Dhl\Versenden\Api\Service\ServiceFactory;
use \Dhl\Versenden\Api\Webservice\BcsAccessDataInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Session\SessionManagerInterface;

/**
 * DisableCodPaymentObserver
 *
 * @category Dhl
 * @package  Dhl\Versenden
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
     * @var BcsAccessDataInterface
     */
    private $bcsAccessData;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ServiceCollectionFactory
     */
    private $serviceCollectionFactory;

    /**
     * DisableCodPaymentObserver constructor.
     *
     * @param ModuleConfigInterface    $config
     * @param BcsAccessDataInterface   $bcsAccessData
     * @param SessionManagerInterface  $checkoutSession
     * @param ServiceCollectionFactory $serviceCollectionFactory
     */
    public function __construct(
        ModuleConfigInterface $config,
        BcsAccessDataInterface $bcsAccessData,
        SessionManagerInterface $checkoutSession,
        ServiceCollectionFactory $serviceCollectionFactory
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->bcsAccessData = $bcsAccessData;
    }

    /**
     * Disable COD payment methods if it is not available for the current
     * GK API product.
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
        $paymentMethod  = $methodInstance->getCode();

        if (!$this->config->canProcessMethod($shippingMethod, $quote->getStoreId())) {
            // no dhl shipping method
            return;
        }

        if (!$this->config->isCodPaymentMethod($paymentMethod, $quote->getStoreId())) {
            // no cod payment method
            return;
        }

        // obtain possible dhl products (national, weltpaket, …) and check if COD is allowed
        $shipperCountry   = $this->config->getShipperCountry($quote->getStoreId());
        $recipientCountry = $quote->getShippingAddress()->getCountryId();
        $euCountries      = $this->config->getEuCountryList();

        //FIXME(nr): allow cross-border shipping
        if (!in_array($recipientCountry, $this->config->getEuCountryList())) {
            return;
        }

        $usedProduct = $this->bcsAccessData->getProductCode($shipperCountry, $recipientCountry, $euCountries);

        $codService = ServiceFactory::get(Cod::CODE);
        $productFilter = ProductFilter::create(['code' => $usedProduct]);

        /** @var ServiceCollection $collection */
        $collection = $this->serviceCollectionFactory->create(['services' => [$codService]]);
        $items = $collection->filter($productFilter);
        $checkResult->setData('is_available', isset($items[Cod::CODE]));
    }
}
