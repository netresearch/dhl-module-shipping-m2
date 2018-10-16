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
namespace Dhl\Shipping\Model\Service\Option;

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Model\Config\ServiceConfigInterface;
use Dhl\Shipping\Model\Service\StartDate;
use Dhl\Shipping\Service\Bcs\PreferredTime;
use Dhl\Shipping\Webservice\ParcelManagement;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PreferredTimeOptionProvider implements OptionProviderInterface
{

    const POSTAL_CODE = 'postalCode';

    const SERVICE_CODE = PreferredTime::CODE;

    /**
     * @var ParcelManagement
     */
    private $parcelManagement;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ServiceConfigInterface
     */
    private $serviceConfig;

    /**
     * @var StartDate
     */
    private $startDateModel;

    /**
     * PreferredDayOptionProvider constructor.
     * @param ParcelManagement $parcelManagement
     * @param CheckoutSession $checkoutSession
     * @param DateTimeFactory $dateTimeFactory
     * @param ServiceConfigInterface $serviceConfig
     * @param StartDate $startDateModel
     */
    public function __construct(
        ParcelManagement $parcelManagement,
        CheckoutSession $checkoutSession,
        DateTimeFactory $dateTimeFactory,
        ServiceConfigInterface $serviceConfig,
        StartDate $startDateModel
    ) {
        $this->parcelManagement = $parcelManagement;
        $this->checkoutSession = $checkoutSession;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->serviceConfig = $serviceConfig;
        $this->startDateModel = $startDateModel;
    }


    public function enhanceServiceWithOptions($service, $args)
    {
        try {
            $startDate = $this->getStartDate();
            // options from the api
            $timeFrames = $this->parcelManagement->getPreferredTimeOptions($startDate, $args[self::POSTAL_CODE]);
        } catch (\Exception $e) {
            $timeFrames = [];
        }

        $options = [];
        foreach ($timeFrames as $timeFrame) {
            $options[] = [
                'label' =>  $timeFrame->getStart() . '-' . $timeFrame->getEnd(),
                'value' => str_replace(':', '', $timeFrame->getStart() . $timeFrame->getEnd())
            ];
        }

        $service[ServiceSettingsInterface::OPTIONS] = $options;

        return $service;
    }

    /**
     * @return string
     */
    public function getServiceCode()
    {
        return self::SERVICE_CODE;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    private function getStartDate()
    {
        $storeId = $this->checkoutSession->getQuote()->getStoreId();
        $dateModel = $this->dateTimeFactory->create();
        $noDropOffDays = $this->serviceConfig->getExcludedDropOffDays($storeId);
        $cutOffTime = $this->serviceConfig->getCutOffTime($storeId);
        $cutOffTime = $dateModel->gmtTimestamp(str_replace(',', ':', $cutOffTime));
        $currentDate = $dateModel->gmtDate("Y-m-d H:i:s");
        $startDate = $this->startDateModel->getStartdate($currentDate, $cutOffTime, $noDropOffDays);


        return new \DateTime($startDate);
    }


}