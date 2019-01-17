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
namespace Dhl\Shipping\Model;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Config\ServiceChargeConfig;
use Dhl\Shipping\Model\Quote\ServiceSelection;
use Dhl\Shipping\Model\ResourceModel\ServiceSelectionRepository;
use Dhl\Shipping\Service\Bcs\PreferredDay;
use Dhl\Shipping\Service\Bcs\PreferredTime;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

/**
 * Sales Order Total.
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Total extends Address\Total\AbstractTotal
{
    const SERVICE_CHARGE_FIELD_NAME = 'dhl_service_charge';
    const SERVICE_CHARGE_BASE_FIELD_NAME = 'base_dhl_service_charge';

    /**
     * @var string
     */
    protected $_code = 'dhl_service_charge';

    /**
     * @var ServiceSelectionRepository
     */
    private $selectedServiceRepo;

    /**
     * @var ServiceChargeConfig
     */
    private $serviceConfig;

    /**
     * @var ModuleConfigInterface
     */
    private $shippingModuleConfig;

    /**
     * Shipping constructor.
     * @param ServiceSelectionRepository $selectedServiceRepo
     * @param ServiceChargeConfig $serviceConfig
     * @param ModuleConfigInterface $shippingModuleConfig
     */
    public function __construct(
        ServiceSelectionRepository $selectedServiceRepo,
        ServiceChargeConfig $serviceConfig,
        ModuleConfigInterface $shippingModuleConfig
    ) {
        $this->selectedServiceRepo = $selectedServiceRepo;
        $this->serviceConfig = $serviceConfig;
        $this->shippingModuleConfig = $shippingModuleConfig;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getLabel()
    {
        return __('DHL Custom Package Delivery Service Fee');
    }

    /**
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param AddressTotal $total
     * @return $this|AbstractTotal
     */
    public function collect(Quote $quote, ShippingAssignmentInterface $shippingAssignment, Address\Total $total)
    {
        parent::collect($quote, $shippingAssignment, $total);

        $shippingAddress = $shippingAssignment->getShipping()->getAddress();

        if (!$shippingAddress->getId()) {
            return $this;
        }

        $method = $shippingAssignment->getShipping()->getMethod();
        $items = $shippingAssignment->getItems();
        $storeId = $quote->getStoreId();
        $countryId = $shippingAddress->getCountryId();

        if (empty($items) || !$this->shippingModuleConfig->canProcessShipping($method, $countryId, $storeId)) {
            return $this;
        }

        $fee = $this->getServiceSelectionFee($shippingAddress->getId(), $storeId);
        $total->setTotalAmount(self::SERVICE_CHARGE_FIELD_NAME, $fee);
        $total->setBaseTotalAmount(self::SERVICE_CHARGE_FIELD_NAME, $fee);
        $total->setData(self::SERVICE_CHARGE_FIELD_NAME, $fee);
        $total->setData(self::SERVICE_CHARGE_BASE_FIELD_NAME, $fee);
        $quote->setData(self::SERVICE_CHARGE_FIELD_NAME, $fee);
        $quote->setData(self::SERVICE_CHARGE_BASE_FIELD_NAME, $fee);
        $quote->setGrandTotal($total->getGrandTotal() + $fee);
        $quote->setBaseGrandTotal($total->getBaseGrandTotal() + $fee);

        return $this;
    }

    /**
     * @param Quote $quote
     * @param AddressTotal $total
     * @return mixed[]
     */
    public function fetch(Quote $quote, Address\Total $total)
    {
        $result = [];
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getId()) {
            return $result;
        }

        $method = $shippingAddress->getShippingMethod();
        $countryId = $shippingAddress->getCountryId();
        if (!$this->shippingModuleConfig->canProcessShipping($method, $countryId, $quote->getStoreId())) {
            return $result;
        }

        $fee = $this->getServiceSelectionFee($shippingAddress->getId(), $quote->getStoreId());
        if ($fee > 0.0) {
            $result = [
                'code' => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $fee
            ];
        }

        return $result;
    }

    /**
     * @param string $addressId
     * @param string $storeId
     * @return float
     */
    private function getServiceSelectionFee($addressId, $storeId)
    {
        try {
            /** @var ServiceSelection[] $serviceSelection */
            $serviceSelection = $this->selectedServiceRepo->getByQuoteAddressId($addressId)
                ->addFieldToFilter(
                    'service_code',
                    ['in' => [PreferredDay::CODE, PreferredTime::CODE]]
                )
                ->getItems();
        } catch (\Exception $e) {
            $serviceSelection = [];
        }

        $fee = 0.0;
        if (count($serviceSelection) === 2) {
            // combined mode
            $fee = (float)$this->serviceConfig->getCombinedCharge($storeId);
        }

        if (count($serviceSelection) === 1) {
            $selectedService = array_shift($serviceSelection);
            if ($selectedService->getServiceCode() === PreferredDay::CODE) {
                $fee = (float)$this->serviceConfig->getPreferredDayCharge($storeId);
            }
            if ($selectedService->getServiceCode() === PreferredTime::CODE) {
                $fee = (float)$this->serviceConfig->getPreferredTimeCharge($storeId);
            }
        }

        return $fee;
    }
}
