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
 * @package   Dhl\Shipping\Webservice
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Webservice;

use Dhl\Sdk\Paket\ParcelManagement\Api\Data\CarrierServiceInterface;
use Dhl\Sdk\Paket\ParcelManagement\Api\Data\TimeFrameOptionInterface;
use Dhl\Sdk\Paket\ParcelManagement\Exception\ServiceException;
use Dhl\Sdk\Paket\ParcelManagement\Service\ServiceFactory;
use Dhl\Shipping\Config\BcsConfigInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Util\Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Parcel Management API Client wrapper.
 *
 * @package  Dhl\Shipping\Webservice
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ParcelManagement
{
    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var BcsConfigInterface
     */
    private $bcsConfig;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var CarrierServiceInterface[][]
     */
    private $services = [];

    /**
     * ParcelManagement constructor.
     *
     * @param ServiceFactory $serviceFactory
     * @param ModuleConfigInterface $moduleConfig
     * @param BcsConfigInterface $bcsConfig
     * @param Logger $logger
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        ModuleConfigInterface $moduleConfig,
        BcsConfigInterface $bcsConfig,
        Logger $logger
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->moduleConfig = $moduleConfig;
        $this->bcsConfig = $bcsConfig;
        $this->logger = $logger;
    }

    /**
     * @param \DateTime $dropOffDate
     * @param string $postalCode
     * @param int $storeId
     * @return CarrierServiceInterface[]
     * @throws LocalizedException
     */
    private function getCheckoutServices($dropOffDate, $postalCode, $storeId)
    {
        $checkoutService = $this->serviceFactory->createCheckoutService(
            $this->bcsConfig->getAuthUsername($storeId),
            $this->bcsConfig->getAuthPassword($storeId),
            $this->bcsConfig->getAccountEkp($storeId),
            $this->logger,
            $this->moduleConfig->isSandboxModeEnabled($storeId)
        );

        try {
            $carrierServices = $checkoutService->getCarrierServices($postalCode, $dropOffDate);
        } catch (ServiceException $exception) {
            throw new LocalizedException(__('Parcel Management API error occurred'), $exception);
        }

        // add service codes as array keys
        $carrierServiceCodes = array_map(
            function (CarrierServiceInterface $carrierService) {
                return $carrierService->getCode();
            },
            $carrierServices
        );

        $carrierServices = array_combine($carrierServiceCodes, $carrierServices);

        return $carrierServices;
    }

    /**
     * @param \DateTime $dropOffDate Day when the shipment will be dropped by the sender in the DHL parcel center
     * @param string $postalCode
     * @param int $storeId
     * @return string[][]
     * @throws LocalizedException
     */
    public function getPreferredDayOptions($dropOffDate, $postalCode, $storeId)
    {
        if (empty($this->services[$storeId])) {
            $this->services[$storeId] = $this->getCheckoutServices($dropOffDate, $postalCode, $storeId);
        }

        if (!isset($this->services[$storeId]['preferredDay'])
            || !$this->services[$storeId]['preferredDay']->isAvailable()) {
            throw new LocalizedException(__('There are no results for this service.'));
        }

        $options = [];

        $validDays = $this->services[$storeId]['preferredDay']->getOptions();
        foreach ($validDays as $validDay) {
            try {
                $startDay = new \DateTime($validDay->getStart());
            } catch (\Exception $e) {
                continue;
            }

            $options[] = [
                'label' => __($startDay->format('D')) . ', ' . $startDay->format('d.'),
                'value' => $startDay->format('Y-m-d'),
                'disable' => false,
            ];
        }

        return $options;
    }

    /**
     * @param \DateTime $dropOffDate Day when the shipment will be dropped by the sender in the DHL parcel center
     * @param string $postalCode
     * @param int $storeId
     * @return string[][]
     * @throws LocalizedException
     */
    public function getPreferredTimeOptions($dropOffDate, $postalCode, $storeId)
    {
        if (empty($this->services[$storeId])) {
            $this->services[$storeId] = $this->getCheckoutServices($dropOffDate, $postalCode, $storeId);
        }

        if (!isset($this->services[$storeId]['preferredTime'])
            || !$this->services[$storeId]['preferredTime']->isAvailable()) {
            throw new LocalizedException(__('There are no results for this service.'));
        }

        $options = [];

        /** @var TimeFrameOptionInterface[] $timeFrames */
        $timeFrames = $this->services[$storeId]['preferredTime']->getOptions();
        foreach ($timeFrames as $timeFrame) {
            $options[] = [
                'label' => $timeFrame->getStart() . '-' . $timeFrame->getEnd(),
                'value' => str_replace(':', '', $timeFrame->getStart() . $timeFrame->getEnd()),
            ];
        }

        return $options;
    }
}
