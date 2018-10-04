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

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterfaceFactory;
use Dhl\Shipping\Api\ServicePoolInterface;
use Dhl\Shipping\Api\ServiceSelectionRepositoryInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Service\ServiceCollection;
use Dhl\Shipping\Service\Filter\CodServiceFilter;
use Dhl\Shipping\Service\Filter\RouteFilter;
use Dhl\Shipping\Util\ShippingRoutes\RouteValidatorInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @var ServicePoolInterface
     */
    private $servicePool;

    /**
     * @var RouteValidatorInterface
     */
    private $routeValidator;

    /**
     * @var ServiceSettingsInterfaceFactory
     */
    private $serviceSettingsFactory;

    /**
     * @var ServiceSelectionRepositoryInterface
     */
    private $serviceSelectionRepository;

    /**
     * DisableCodPaymentObserver constructor.
     *
     * @param ModuleConfigInterface $config
     * @param SessionManagerInterface $checkoutSession
     * @param ServicePoolInterface $servicePool
     * @param RouteValidatorInterface $routeValidator
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     * @param ServiceSelectionRepositoryInterface $serviceSelectionRepository
     */
    public function __construct(
        ModuleConfigInterface $config,
        SessionManagerInterface $checkoutSession,
        ServicePoolInterface $servicePool,
        RouteValidatorInterface $routeValidator,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory,
        ServiceSelectionRepositoryInterface $serviceSelectionRepository
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->servicePool = $servicePool;
        $this->routeValidator = $routeValidator;
        $this->serviceSettingsFactory = $serviceSettingsFactory;
        $this->serviceSelectionRepository = $serviceSelectionRepository;
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

        $canShipWithCod = $this->filterCodService($quote, $recipientCountry);
        $checkResult->setData('is_available', $canShipWithCod);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    private function filterCodService(\Magento\Quote\Model\Quote $quote, $recipientCountry)
    {
        $shipperCountry = $this->config->getShipperCountry($quote->getStoreId());
        $euCountries = $this->config->getEuCountryList();

        // fetch all COD services
        $codServiceSettings = [
            ServicePoolInterface::SERVICE_COD_CODE =>
                $this->config->getServiceSettings(
                    $quote->getStoreId()
                )[ServicePoolInterface::SERVICE_COD_CODE],
        ];
        $codServiceSettings = array_map(
            function ($config) {
                return $this->serviceSettingsFactory->create($config);
            },
            $codServiceSettings
        );
        /** @var ServiceCollection $codServices */
        $codServices = $this->servicePool->getServices($codServiceSettings);

        // filter cod services by route
        $routeFilter = RouteFilter::create($this->routeValidator, $shipperCountry, $recipientCountry, $euCountries);
        $codServices = $codServices->filter($routeFilter);

        try {
            $selectedServices = $this->serviceSelectionRepository
                ->getByQuoteAddressId($quote->getShippingAddress()->getId());
            $selectedServices = $selectedServices->getItems();
        } catch (NoSuchEntityException $e) {
            $selectedServices = [];
        }

        $codServiceFilter = CodServiceFilter::create($selectedServices);
        $codServices = $codServices->filter($codServiceFilter);

        return $codServices->count() > 0;
    }
}
