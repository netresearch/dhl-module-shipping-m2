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
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Service\Filter;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Service\ServiceCollection;
use Dhl\Shipping\Service\Filter\CustomerSelectionFilter;
use Dhl\Shipping\Service\Filter\RouteFilter;
use Dhl\Shipping\Util\ShippingRoutes\RouteValidatorInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class CheckoutServiceFilter
 *
 * @package Dhl\Shipping\Model\Service\Filter
 * @author Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @link http://www.netresearch.de/
 */
class CheckoutServiceFilter
{
    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var RouteValidatorInterface
     */
    private $routeValidator;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var SessionManagerInterface|CheckoutSession
     */
    private $checkoutSession;

    /**
     * CheckoutServiceFilter constructor.
     *
     * @param ModuleConfigInterface $config
     * @param RouteValidatorInterface $routeValidator
     * @param StockRegistryInterface $stockRegistry
     * @param CheckoutSession|SessionManagerInterface $checkoutSession
     */
    public function __construct(
        ModuleConfigInterface $config,
        RouteValidatorInterface $routeValidator,
        StockRegistryInterface $stockRegistry,
        SessionManagerInterface $checkoutSession
    ) {
        $this->config = $config;
        $this->routeValidator = $routeValidator;
        $this->stockRegistry = $stockRegistry;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Applies the following filters on the serviceCollection:
     *  - CustomerSelectionFilter
     *  - RouteFilter
     *  - InStockFilter
     *
     * @param ServiceCollection $serviceCollection
     * @param $storeId
     * @param string $countryId
     * @return ServiceCollection
     */
    public function filterServiceCollection(ServiceCollection $serviceCollection, $storeId, $countryId)
    {
        $checkoutFilter = CustomerSelectionFilter::create();
        $routeFilter = RouteFilter::create(
            $this->routeValidator,
            $this->config->getShipperCountry($storeId),
            $countryId,
            $this->config->getEuCountryList($storeId)
        );

        $cartItems = $this->checkoutSession->getQuote()->getItems();
        $inStockFilter = InStockFilter::create($cartItems, $this->stockRegistry);

        $serviceCollection = $serviceCollection->filter($checkoutFilter);
        $serviceCollection = $serviceCollection->filter($routeFilter);
        $serviceCollection = $serviceCollection->filter($inStockFilter);

        return $serviceCollection;
    }
}