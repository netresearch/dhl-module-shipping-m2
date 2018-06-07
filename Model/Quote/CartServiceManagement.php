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

use Dhl\Shipping\Api\Data\ServiceInformationInterface;
use Dhl\Shipping\Api\Data\ServiceInformationInterfaceFactory;
use Dhl\Shipping\Api\Data\ServiceSelectionInterface;
use Dhl\Shipping\Api\Quote\CartServiceManagementInterface;
use Dhl\Shipping\Model\Config\ModuleConfig;
use Dhl\Shipping\Model\ResourceModel\ServiceSelectionRepository;
use Dhl\Shipping\Model\Service\CheckoutServiceProvider;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ServiceInformationInterfaceFactory
     */
    private $serviceInformationFactory;

    /**
     * CartServiceManagement constructor.
     *
     * @param CheckoutServiceProvider $checkoutServiceProvider
     * @param QuoteRepository $quoteRepository
     * @param ModuleConfig $moduleConfig
     * @param ServiceSelectionRepository $serviceSelectionRepo
     * @param ServiceSelectionFactory $serviceSelectionFactory
     * @param Escaper $escaper
     * @param ServiceInformationInterfaceFactory $serviceInformationFactory
     */
    public function __construct(
        CheckoutServiceProvider $checkoutServiceProvider,
        QuoteRepository $quoteRepository,
        ModuleConfig $moduleConfig,
        ServiceSelectionRepository $serviceSelectionRepo,
        ServiceSelectionFactory $serviceSelectionFactory,
        Escaper $escaper,
        ServiceInformationInterfaceFactory $serviceInformationFactory
    ) {
        $this->checkoutServiceProvider = $checkoutServiceProvider;
        $this->quoteRepository = $quoteRepository;
        $this->moduleConfig = $moduleConfig;
        $this->serviceSelectionRepository = $serviceSelectionRepo;
        $this->serviceSelectionFactory = $serviceSelectionFactory;
        $this->escaper = $escaper;
        $this->serviceInformationFactory = $serviceInformationFactory;
    }

    /**
     * @param int $cartId
     * @param string $countryId
     * @param string $shippingMethod
     * @return ServiceInformationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     */
    public function getServices($cartId, $countryId, $shippingMethod)
    {
        $quote = $this->quoteRepository->get($cartId);
        $dhlShippingMethod = $this->moduleConfig->getShippingMethods($quote->getStoreId());

        if (in_array($shippingMethod, $dhlShippingMethod)) {
            $services = $this->checkoutServiceProvider->getServices($countryId, $quote->getStoreId());
            $compatibility = $this->checkoutServiceProvider->getCompatibility($countryId, $quote->getStoreId());
        } else {
            $services = $compatibility = [];
        }

        return $this->serviceInformationFactory->create(['data' => [
            'services' => $services,
            'compatibility' => $compatibility,
        ]]);
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
                'service_value' => $this->getEscapedValues($service->getValue()),
            ]);
            $this->serviceSelectionRepository->save($model);
        }
    }

    /**
     * Load a service selection by cart ID.
     *
     * @param string $cartId
     * @return ServiceSelectionInterface[]
     */
    public function load($cartId)
    {
        try {
            $quote = $this->quoteRepository->get($cartId);
            $quoteAddressId = $quote->getShippingAddress()->getId();

            return $this->serviceSelectionRepository->getByQuoteAddressId($quoteAddressId)->getItems();
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }

    /**
     * Validate a service selection's compatibility.
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $serviceSelection
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validate($serviceSelection)
    {
        throw new \Magento\Framework\Exception\ValidatorException(__('Service %1 and %2 can not be chosen together.'));
        throw new \Magento\Framework\Exception\ValidatorException(__('Service %1 can only be chosen with service %2.'));
    }

    /**
     * HTML escape all values in service returned from frontend.
     *
     * @param array $values
     * @return array
     */
    private function getEscapedValues(array $values): array
    {
        return array_map(function ($value) {
            return $this->escaper->escapeHtml($value);
        }, $values);
    }
}
