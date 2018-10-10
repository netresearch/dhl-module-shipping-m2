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
namespace Dhl\Shipping\Model\Service;

use Dhl\ParcelManagement\ApiException;
use Dhl\ParcelManagement\Model\AvailableServicesMap;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\Service\VisualCheckOfAge as VisualCheckOfAgeOptions;
use Dhl\Shipping\Model\Config\ServiceConfigInterface;
use Dhl\Shipping\Webservice\Client\PmRestClient;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Provide Service Options for Checkout Services.
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceOptionProvider
{

    const NON_WORKING_DAY = 'Sun';

    const API_RESPONSE_CACHE_IDENT = 'pmApiResponse';

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ServiceConfigInterface
     */
    private $serviceConfig;

    /**
     * @var null|AvailableServicesMap
     */
    private $serviceResponse = null;

    /**
     * @var SessionManagerInterface|CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var StartDate
     */
    private $startDateModel;

    /**
     * @var PmRestClient
     */
    private $checkoutApi;

    /**
     * ServiceOptionProvider constructor.
     * @param DateTimeFactory $dateTimeFactory
     * @param SessionManagerInterface $checkoutSession
     * @param ServiceConfigInterface $serviceConfig
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        SessionManagerInterface $checkoutSession,
        ServiceConfigInterface $serviceConfig,
        StartDate $startDateModel,
        PmRestClient $checkoutApi
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->checkoutSession = $checkoutSession;
        $this->serviceConfig = $serviceConfig;
        $this->startDateModel = $startDateModel;
        $this->checkoutApi = $checkoutApi;
    }

    /**
     * @return array
     */
    public function getPreferredDayOptions(): array
    {
        $options = [];
        $disabled = false;
        $options[] = [
            'label' => 'foo',
            'value' => '234234',
            'disabled' => $disabled
        ];


        $options = $this->getAvailableServicesForRecipientZip();
        $validDays = $options->getPreferredDay()->getValidDays();

        return $validDays;
    }

    /**
     * @return string[]
     */
    public function getPreferredTimeOptions(): array
    {
        $options = [
            [
                'label' => __('18:00–20:00'),
                'value' => '18002000'
            ],
            [
                'label' => __('19:00–21:00'),
                'value' => '19002100'
            ]
        ];

        return $options;
    }

    /**
     * @return string[]
     */
    public function getVisualCheckOfAgeOptions(): array
    {
        return [
            [
                'label' => VisualCheckOfAgeOptions::OPTION_A16,
                'value' => VisualCheckOfAgeOptions::OPTION_A16,
            ],
            [
                'label' => VisualCheckOfAgeOptions::OPTION_A18,
                'value' => VisualCheckOfAgeOptions::OPTION_A18,
            ],
        ];
    }


    /**
     * @return AvailableServicesMap|null
     */
    private function getAvailableServicesForRecipientZip()
    {
        if ($this->serviceResponse === null) {
            try  {
                $this->serviceResponse = $this->checkoutApi->getCheckoutServices(
                    $this->getStartDate(),
                    $this->getZipCode()
                );
            } catch (\Exception $e) {
                $this->serviceResponse = null;
            }
        }

        return $this->serviceResponse;
    }


    /**
     * @return \DateTime
     * @throws \Exception
     */
    private function getStartDate()
    {
        $storeId = $this->checkoutSession->getQuote()->getStoreId();
        $dateModel     = $this->dateTimeFactory->create();
        $noDropOffDays = $this->serviceConfig->getExcludedDropOffDays($storeId);
        $cutOffTime  = $this->serviceConfig->getCutOffTime($storeId);
        $cutOffTime  = $dateModel->gmtTimestamp(str_replace(',', ':', $cutOffTime));
        $currentDate = $dateModel->gmtDate("Y-m-d H:i:s");
        $startDate   = $this->startDateModel->getStartdate($currentDate, $cutOffTime, $noDropOffDays);


        return new \DateTime($startDate);
    }

    /**
     * @return string
     */
    private function getZipCode()
    {
        $quote = $this->checkoutSession->getQuote();
        return $quote->getShippingAddress()->getPostcode();
    }
}
