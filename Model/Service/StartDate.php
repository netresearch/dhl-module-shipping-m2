<?php

namespace Dhl\Shipping\Model\Service;

use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Yasumi\Yasumi;

class StartDate
{
    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var ResolverInterfaceFactory
     */
    private $localeResolverFactory;

    /**
     * StartDate constructor.
     * @param DateTimeFactory $dateTimeFactory
     * @param ResolverInterfaceFactory $localeResolverFactory
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory,
        ResolverInterfaceFactory $localeResolverFactory
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
        $this->localeResolverFactory = $localeResolverFactory;
    }

    /**
     * @param $date
     * @param $cutOffTime
     * @param $noDropOffDays
     * @return string
     * @throws \Exception
     */
    public function getStartDate($date, $cutOffTime, $noDropOffDays)
    {
        $timeformat = 'Y-m-d H:i:s';
        $isInCt = $this->isInCutOffTime($date, $cutOffTime);
        $isWdAv = $this->isAvailable($date, $noDropOffDays);

        if ($isInCt && $isWdAv) {
            $startDate = $date;
        } else {
            $end = 2;
            for ($i = 1; $i < $end; $i++) {
                /** @var \DateTime $datetime */
                $datetime = new \DateTime($date);
                $tmpDate = $datetime->add(new \DateInterval("P{$i}D"));
                $nextPossibleDay = $tmpDate->format($timeformat);
                $isAvailble = $this->isAvailable($nextPossibleDay, $noDropOffDays);
                $isAvailble ? $end-- : $end++;
            }

            $startDate = $nextPossibleDay;
        }

        return $startDate;
    }

    /**
     * @param $date
     * @param $noDropOffDays
     * @return bool
     * @throws \ReflectionException
     */
    private function isAvailable($date, $noDropOffDays)
    {
        $locale = $this->localeResolverFactory->create()->getLocale();
        $dateModel = $this->dateTimeFactory->create();
        $year = $dateModel->date('Y');
        $holidayProvider = Yasumi::create('Germany', $year, $locale);
        $weekday = (int)$dateModel->gmtDate('w', $date);
        $isSunday = $weekday === 0;
        $isHoliday = $holidayProvider->isHoliday(new \DateTime($date));
        $isDropoffDay = $this->isDropOffDay($weekday, $noDropOffDays);

        return !$isSunday && !$isHoliday && $isDropoffDay;
    }

    /**
     * @param string $date
     * @param string $cutOffTime
     * @return bool
     */
    private function isInCutOffTime($date, $cutOffTime)
    {
        $dateModel = $this->dateTimeFactory->create();
        return $cutOffTime > $dateModel->gmtTimestamp($date);
    }

    /**
     * @param int $weekday
     * @param string $noDropOffDays
     * @return bool
     */
    private function isDropOffDay($weekday, $noDropOffDays)
    {
        $noDropOffDayArray = explode(',', $noDropOffDays);

        return !in_array((string)$weekday, $noDropOffDayArray, true);
    }
}
