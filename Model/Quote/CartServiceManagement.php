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
use Dhl\Shipping\Api\Quote\CartServiceManagementInterface;
use Dhl\Shipping\Model\Config\ModuleConfig;
use Dhl\Shipping\Model\ResourceModel\ServiceSelectionRepository;
use Dhl\Shipping\Model\Service\CheckoutServiceProvider;
use Magento\Framework\Escaper;
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
     * @param string $postalCode
     * @return ServiceInformationInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getServices($cartId, $countryId, $shippingMethod, $postalCode)
    {
        $quote = $this->quoteRepository->get($cartId);
        $canProcess = $this->moduleConfig->canProcessMethod($shippingMethod, $quote->getStoreId());

        if ($canProcess && $this->moduleConfig->getShipperCountry($quote->getStoreId()) !== 'AT') {
            $services = $this->checkoutServiceProvider->getServices($countryId, $quote->getStoreId(), $postalCode);
            $compatibility = $this->checkoutServiceProvider->getCompatibility($countryId, $quote->getStoreId());
        } else {
            $services = $compatibility = [];
        }

        $methods =  $this->moduleConfig->getShippingMethods($quote->getStoreId());

        return $this->serviceInformationFactory->create(
            [
                'data' => [
                    'services' => $services,
                    'compatibility' => $compatibility,
                    'methods' => $methods,
                ],
            ]
        );
    }

    /**
     * Persist service selection with reference to a Quote Address ID.
     *
     * @param int $cartId
     * @param \Magento\Framework\Api\AttributeInterface[] $serviceSelection
     * @param string $shippingMethod
     * @throws \Magento\Framework\Exception\NoSuchEntityException;
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save($cartId, $serviceSelection, $shippingMethod)
    {
        $quote = $this->quoteRepository->get($cartId);
        $quoteAddressId = $quote->getShippingAddress()->getId();

        $canProcess = $this->moduleConfig->canProcessMethod($shippingMethod, $quote->getStoreId());
        $this->serviceSelectionRepository->deleteByQuoteAddressId($quoteAddressId);

        if ($canProcess) {
            foreach ($serviceSelection as $service) {
                $model = $this->serviceSelectionFactory->create();
                $model->setData(
                    [
                        'parent_id' => $quoteAddressId,
                        'service_code' => $service->getAttributeCode(),
                        'service_value' => $this->getEscapedValues($service->getValue()),
                    ]
                );
                $this->serviceSelectionRepository->save($model);
            }
        }
    }

    /**
     * HTML escape all values in service returned from frontend.
     *
     * @param array $values
     * @return array
     */
    private function getEscapedValues(array $values)
    {
        return array_map(
            function ($value) {
                return $this->escaper->escapeHtml($value);
            },
            $values
        );
    }
}
