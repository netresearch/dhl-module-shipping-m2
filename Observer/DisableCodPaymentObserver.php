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

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterfaceFactory;
use Dhl\Shipping\Api\ServicePoolInterface;
use Dhl\Shipping\Api\ServiceSelectionRepositoryInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Service\Filter\CodServiceFilter;
use Dhl\Shipping\Model\Service\ServiceCollection;
use Dhl\Shipping\Model\Service\ServiceConfig;
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
     * @var ServiceConfig
     */
    private $serviceConfig;

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
     * @param ServiceConfig $serviceConfig
     * @param SessionManagerInterface $checkoutSession
     * @param ServicePoolInterface $servicePool
     * @param RouteValidatorInterface $routeValidator
     * @param ServiceSettingsInterfaceFactory $serviceSettingsFactory
     * @param ServiceSelectionRepositoryInterface $serviceSelectionRepository
     */
    public function __construct(
        ModuleConfigInterface $config,
        ServiceConfig $serviceConfig,
        SessionManagerInterface $checkoutSession,
        ServicePoolInterface $servicePool,
        RouteValidatorInterface $routeValidator,
        ServiceSettingsInterfaceFactory $serviceSettingsFactory,
        ServiceSelectionRepositoryInterface $serviceSelectionRepository
    ) {
        $this->config = $config;
        $this->serviceConfig = $serviceConfig;
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

        $canShipWithCod = $this->validateCodAvailibility($quote, $recipientCountry);
        $checkResult->setData('is_available', $canShipWithCod);
    }

    /**
     * Validates if cash on delivery service is available for route and customer selection
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param                            $recipientCountry
     *
     * @return bool
     */
    private function validateCodAvailibility(\Magento\Quote\Model\Quote $quote, $recipientCountry)
    {
        $shipperCountry = $this->config->getShipperCountry($quote->getStoreId());
        $euCountries = $this->config->getEuCountryList();

        $codServiceSettings = $this->prepareCodServiceSettings($quote);

        /** @var ServiceCollection $codServices */
        $codServices = $this->servicePool->getServices($codServiceSettings);

        // filter cod services by route
        $routeFilter = RouteFilter::create($this->routeValidator, $shipperCountry, $recipientCountry, $euCountries);
        $codServices = $codServices->filter($routeFilter);

        if ($codServices->count() > 0) {
            // cod is theoretically possible, check against customer preselection
            return $this->validateCustomerSelection($quote, $codServices);
        }

        // no cod services available for given route anyway, skip DB query
        return false;
    }

    /**
     * Extract cash on delivery settings from config model
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return ServiceSettingsInterface[]
     */
    private function prepareCodServiceSettings(\Magento\Quote\Model\Quote $quote)
    {
        // fetch all COD services
        $codServiceSettings = [
            ServicePoolInterface::SERVICE_COD_CODE =>
                $this->serviceConfig->getServiceSettings(
                    $quote->getStoreId()
                )[ServicePoolInterface::SERVICE_COD_CODE],
        ];
        $codServiceSettings = array_map(
            function ($config) {
                return $this->serviceSettingsFactory->create($config);
            },
            $codServiceSettings
        );

        return $codServiceSettings;
    }

    /**
     * Validates if all services selected by customer in checkout are compatible with cash on delivery
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param $codServices
     * @return bool
     */
    private function validateCustomerSelection(\Magento\Quote\Model\Quote $quote, $codServices)
    {
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
