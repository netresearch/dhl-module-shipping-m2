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
use Dhl\Shipping\Service\Bcs\VisualCheckOfAge;
use Dhl\Shipping\Webservice\ParcelManagement;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\Service\VisualCheckOfAge as VisualCheckOfAgeOptions;

class VisualCheckOfAgeOptionProvider implements OptionProviderInterface
{

    const SERVICE_CODE = VisualCheckOfAge::CODE;

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
        $options = [
            [
                'label' => VisualCheckOfAgeOptions::OPTION_A16,
                'value' => VisualCheckOfAgeOptions::OPTION_A16,
            ],
            [
                'label' => VisualCheckOfAgeOptions::OPTION_A18,
                'value' => VisualCheckOfAgeOptions::OPTION_A18,
            ],
        ];

        $service[ServiceSettingsInterface::OPTIONS] = $options;
    }
}