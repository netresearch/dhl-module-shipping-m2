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

use Dhl\Shipping\Model\Config\ServiceConfigInterface;
use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Yasumi\Yasumi;

/**
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class StartDate
{
    const TIME_FORMAT = 'Y-m-d H:i:s';

    const SUNDAY_WEEKDAY_VALUE = '0';

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ResolverInterfaceFactory
     */
    private $localeResolverFactory;

    /**
     * @var ServiceConfigInterface
     */
    private $serviceConfig;

    /**
     * StartDate constructor.
     * @param DateTimeFactory $dateTimeFactory
     * @param ResolverInterfaceFactory $localeResolverFactory
     * @param ServiceConfigInterface $serviceConfig
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        ResolverInterfaceFactory $localeResolverFactory,
        ServiceConfigInterface $serviceConfig
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->localeResolverFactory = $localeResolverFactory;
        $this->serviceConfig = $serviceConfig;
    }

    /**
     * @param $storeId
     * @return \DateTime
     * @throws \Exception
     */
    public function getStartDate($storeId)
    {
        $dateModel = $this->dateTimeFactory->create();
        $currentDateTime = $dateModel->gmtDate("Y-m-d H:i:s");
        $isDateLowerThanCutOffTime = $this->isDateLowerThanCutOffTime($currentDateTime, $storeId);
        $dateIsAvailable = $this->isCurrentDateAvailable($currentDateTime, $storeId);

        $nextPossibleDay = '';
        if ($isDateLowerThanCutOffTime && $dateIsAvailable) {
            $startDate = $currentDateTime;
        } else {
            for ($i = 1; $i < 2; $i++) {
                $datetime = new \DateTime($currentDateTime);
                $tmpDate = $datetime->add(new \DateInterval("P{$i}D"));
                $nextPossibleDay = $tmpDate->format(self::TIME_FORMAT);
                $isAvailable = $this->isCurrentDateAvailable($nextPossibleDay, $storeId);
                $isAvailable ? $i-- : $i++;
            }

            $startDate = $nextPossibleDay;
        }

        return new \DateTime($startDate);
    }

    /**
     * @param $date
     * @param string $storeId
     * @return bool
     * @throws \ReflectionException
     */
    private function isCurrentDateAvailable($date, $storeId)
    {
        $locale = $this->localeResolverFactory->create()->getLocale();
        $excludedDropOffDays = $this->serviceConfig->getExcludedDropOffDays($storeId);
        $dateModel = $this->dateTimeFactory->create();
        $year = $dateModel->date('Y');
        $holidayProvider = Yasumi::create('Germany', $year, $locale);
        $weekday = $dateModel->gmtDate('w', $date);
        $isSunday = $weekday === self::SUNDAY_WEEKDAY_VALUE;
        $isHoliday = $holidayProvider->isHoliday(new \DateTime($date));
        $isDropOffDay = $this->isWeekDayADropOffDay($weekday, $excludedDropOffDays);

        return !$isSunday && !$isHoliday && $isDropOffDay;
    }

    /**
     * @param string $date
     * @param string $storeId
     * @return bool
     */
    private function isDateLowerThanCutOffTime($date, $storeId)
    {
        $dateModel  = $this->dateTimeFactory->create();
        $cutOffTime = $this->serviceConfig->getCutOffTime($storeId);
        $cutOffTime = $dateModel->gmtTimestamp(str_replace(',', ':', $cutOffTime));

        return $cutOffTime > $dateModel->gmtTimestamp($date);
    }

    /**
     * @param string $weekday
     * @param string $excludedDropOffDays
     * @return bool
     */
    private function isWeekDayADropOffDay($weekday, $excludedDropOffDays)
    {
        $noDropOffDayArray = explode(',', $excludedDropOffDays);
        return !in_array($weekday, $noDropOffDayArray, true);
    }
}
