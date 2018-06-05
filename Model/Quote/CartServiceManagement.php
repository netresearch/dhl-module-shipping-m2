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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Quote;

use Dhl\Shipping\Api\Data\ShippingInfo\ServiceInterface;
use Dhl\Shipping\Api\Quote\CartServiceManagementInterface;
use Dhl\Shipping\Model\Config\ModuleConfig;
use Dhl\Shipping\Model\ResourceModel\ServiceSelectionRepository;
use Dhl\Shipping\Model\Service\CheckoutServiceProvider;
use Dhl\Shipping\Model\Service\ServiceCollection;
use Magento\Framework\Api\AttributeInterface;
use Magento\Quote\Model\QuoteRepository;

/**
 * Manage Checkout Services
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CartServiceManagement implements CartServiceManagementInterface
{
    /**
     * @var CheckoutServiceProvider
     */
    private $checkoutServiceProvider;

    /**
     * @var QuoteRepository
     */
    private $quoteRepository;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var ServiceSelectionRepository
     */
    private $serviceSelectionRepository;

    /**
     * @var ServiceSelectionFactory
     */
    private $serviceSelectionFactory;

    /**
     * CartServiceManagement constructor.
     *
     * @param CheckoutServiceProvider $checkoutServiceProvider
     * @param QuoteRepository $quoteRepository
     * @param ModuleConfig $moduleConfig
     * @param ServiceSelectionRepository $serviceSelectionRepo
     * @param ServiceSelectionFactory $serviceSelectionFactory
     */
    public function __construct(
        CheckoutServiceProvider $checkoutServiceProvider,
        QuoteRepository $quoteRepository,
        ModuleConfig $moduleConfig,
        ServiceSelectionRepository $serviceSelectionRepo,
        ServiceSelectionFactory $serviceSelectionFactory
    ) {
        $this->checkoutServiceProvider = $checkoutServiceProvider;
        $this->quoteRepository = $quoteRepository;
        $this->moduleConfig = $moduleConfig;
        $this->serviceSelectionRepository = $serviceSelectionRepo;
        $this->serviceSelectionFactory = $serviceSelectionFactory;
    }

    /**
     * @param int $cartId
     * @param string $countryId
     * @param string $shippingMethod
     * @return array|\Dhl\Shipping\Api\Data\ServiceInterface[]|ServiceInterface[]|ServiceCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function getServices($cartId, $countryId, $shippingMethod)
    {
        $quote = $this->quoteRepository->get($cartId);
        $dhlShippingMethod = $this->moduleConfig->getShippingMethods($quote->getStoreId());

        if (!in_array($shippingMethod, $dhlShippingMethod)) {
            return [];
        }
        $services = $this->checkoutServiceProvider->getServices($countryId, $quote->getStoreId());

        return $services;
    }

    /**
     * Persist service selection with reference to a Quote Address ID.
     *
     * @param int $cartId
     * @param \Magento\Framework\Api\AttributeInterface[] $serviceSelection
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($cartId, $serviceSelection)
    {
        $quote = $this->quoteRepository->get($cartId);
        $quoteAddressId = $quote->getShippingAddress()->getId();
        $this->serviceSelectionRepository->deleteByQuoteAddressId($quoteAddressId);
        foreach ($serviceSelection as $service) {
            $model = $this->serviceSelectionFactory->create();
            $model->setData([
                'parent_id' => $quoteAddressId,
                'service_code' => $service->getAttributeCode(),
                'service_value' => $service->getValue(),
            ]);
            $this->serviceSelectionRepository->save($model);
        }
    }
}
