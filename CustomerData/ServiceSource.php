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
 * @package   Dhl\Shipping\CustomerData
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\CustomerData;

use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Service\CheckoutServiceProvider;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * ServiceSource
 *
 * @package Dhl\Shipping\CustomerData
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.netresearch.de/
 */
class ServiceSource implements SectionSourceInterface
{
    /**
     * @var SessionManagerInterface|\Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var CheckoutServiceProvider
     */
    private $serviceProvider;

    /**
     * @param SessionManagerInterface $checkoutSession
     * @param ModuleConfigInterface $moduleConfig
     * @param CheckoutServiceProvider $serviceProvider
     */
    public function __construct(
        SessionManagerInterface $checkoutSession,
        ModuleConfigInterface $moduleConfig,
        CheckoutServiceProvider $serviceProvider
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->moduleConfig  = $moduleConfig;
        $this->serviceProvider = $serviceProvider;
    }

    /**
     * @return \Closure
     */
    private function getTransformCallback()
    {
        // obtain simple serializable data structure
        $transformFn = function (ServiceInterface $service) {
            // todo(nr): add further properties as needed
            return [
                'code' => $service->getCode(),
                'name' => $service->getName(),
                'inputType' => $service->getInputType(),
            ];
        };

        return $transformFn;
    }

    /**
     * Obtain service data for display in checkout, shipping method step
     *
     * @return string[]
     */
    public function getSectionData()
    {
        $quote = $this->checkoutSession->getQuote();
        $dhlShippingMethods = $this->moduleConfig->getShippingMethods($quote->getStoreId());
        $serviceCollection = $this->serviceProvider->getServices($quote);

        $callback = $this->getTransformCallback();
        $services = $serviceCollection->map($callback);

        return [
            'methods' => $dhlShippingMethods,
            'services' => $services
        ];
    }
}
