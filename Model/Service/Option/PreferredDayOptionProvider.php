<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 16.10.18
 * Time: 16:15
 */
namespace Dhl\Shipping\Model\Service\Option;




use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Model\Config\ServiceConfigInterface;
use Dhl\Shipping\Model\Service\StartDate;
use Dhl\Shipping\Service\Bcs\PreferredDay;
use Dhl\Shipping\Webservice\ParcelManagement;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

class PreferredDayOptionProvider implements OptionProviderInterface
{

    const POSTAL_CODE = 'postalCode';

    const SERVICE_CODE = PreferredDay::CODE;

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
            $validDays = $this->parcelManagement->getPreferredDayOptions($startDate, $args[self::POSTAL_CODE]);
        } catch (\Exception $e) {
            $validDays = [];
        }

        foreach ($validDays as $validDay) {
            $options[] = [
                'label' => $validDay->getStart()->format('D,d.'),
                'value' => $validDay->getStart()->format('Y-m-d'),
                'disable' => false
            ];
        }
        $service[ServiceSettingsInterface::OPTIONS] = $options;
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