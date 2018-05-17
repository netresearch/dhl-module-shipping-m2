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

use Dhl\Shipping\Model\Adminhtml\System\Config\Source\Service\VisualCheckOfAge as VisualCheckOfAgeOptions;
use Dhl\Shipping\Model\Config\ConfigAccessor;
use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory;
use Yasumi\Yasumi;

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
    const CUT_OFF_TIME_CONFIG_XML_PATH = 'carriers/dhlshipping/service_preferredday_cutoff_time';
    const NON_WORKING_DAY = 'Sun';

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ConfigAccessor
     */
    private $configAccessor;

    /**
     * @var ResolverInterfaceFactory
     */
    private $localeResolverFactory;

    /**
     * @var TimezoneInterfaceFactory
     */
    private $timezoneFactory;

    /**
     * ServiceOptionProvider constructor.
     * @param DateTimeFactory $dateTimeFactory
     * @param ConfigAccessor $configAccessor
     * @param ResolverInterfaceFactory $resolverFactory
     * @param TimezoneInterfaceFactory $timezoneFactory
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        ConfigAccessor $configAccessor,
        ResolverInterfaceFactory $resolverFactory,
        TimezoneInterfaceFactory $timezoneFactory
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->configAccessor = $configAccessor;
        $this->localeResolverFactory = $resolverFactory;
        $this->timezoneFactory = $timezoneFactory;
    }

    public function getPreferredDayOptions()
    {
        $options = [];
        $daysToCalculate = 5;
        $locale     = $this->localeResolverFactory->create()->getLocale();
        $dateModel  = $this->dateTimeFactory->create();
        $start      = $dateModel->gmtDate("Y-m-d H:i:s");
        $cutOffTime = $this->configAccessor->getConfigValue(self::CUT_OFF_TIME_CONFIG_XML_PATH);
        $cutOffTime = $dateModel->gmtTimestamp(str_replace(',', ':', $cutOffTime));
        $startDate  = ($cutOffTime < $dateModel->gmtTimestamp($start)) ? 3 : 2;
        $endDate    = $startDate + $daysToCalculate;
        $year       = $dateModel->date('Y');
        $holidayProvider = Yasumi::create('Germany', $year, $locale);

        for ($i = $startDate; $i < $endDate; $i++) {
            $disabled  = false;
            $time      = time() + 86400 * $i;
            $dateTime  = $this->timezoneFactory->create()->date($time);
            $dayOfWeek = $dateModel->date("D", $time);

            if ($holidayProvider->isHoliday($dateTime) || ($dayOfWeek === self::NON_WORKING_DAY)) {
                $disabled = true;
                $endDate++;
            }

            $options[] = [
                'label' => $dateModel->date("D d", $time),
                'value' => $dateModel->date("Y-m-d", $time),
                'disabled' => $disabled
            ];
        }

        return $options;
    }

    public function getPreferredTimeOptions()
    {
        $options = [
            [
                'label' => __('18–20'),
                'value' => '18002000'
            ],
            [
                'label' => __('19–21'),
                'value' => '19002100'
            ]
        ];

        return $options;
    }

    public function getVisualCheckOfAgeOptions()
    {
        return [
            VisualCheckOfAgeOptions::OPTION_A16 => VisualCheckOfAgeOptions::OPTION_A16,
            VisualCheckOfAgeOptions::OPTION_A18 => VisualCheckOfAgeOptions::OPTION_A18
        ];
    }
}
