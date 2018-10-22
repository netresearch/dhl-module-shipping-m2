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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterfaceFactory;
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
    const WEEKDAY_SUNDAY = '7';

    /**
     * @var TimezoneInterfaceFactory
     */
    private $timezoneFactory;

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
     * @param ResolverInterfaceFactory $localeResolverFactory
     * @param ServiceConfigInterface $serviceConfig
     * @param TimezoneInterfaceFactory $timezoneFactory
     */
    public function __construct(
        ResolverInterfaceFactory $localeResolverFactory,
        ServiceConfigInterface $serviceConfig,
        TimezoneInterfaceFactory $timezoneFactory
    ) {
        $this->localeResolverFactory = $localeResolverFactory;
        $this->serviceConfig = $serviceConfig;
        $this->timezoneFactory = $timezoneFactory;
    }

    /**
     * Get the start date.
     *
     * @param string $storeId
     * @return \DateTime
     * @throws \Exception
     */
    public function getStartDate($storeId)
    {
        $timeZone = $this->timezoneFactory->create();
        $currentDateTime = $timeZone->date();
        $excludedDropOffDays = $this->serviceConfig->getExcludedDropOffDays($storeId);
        list($hours, $minutes, $seconds) = array_map('intval', $this->serviceConfig->getCutOffTime($storeId));
        $cutOffDateTime = $timeZone->date()->setTime($hours, $minutes, $seconds);

        return $this->getNextPossibleStartDate($currentDateTime, $cutOffDateTime, $excludedDropOffDays);
    }

    /**
     * Determine the next possible start date.
     *
     * @param \DateTime $currentDateTime
     * @param \DateTime$cutOffDateTime
     * @param string[] $excludedDropOffDays
     * @return \DateTime
     * @throws \Exception
     */
    private function getNextPossibleStartDate($currentDateTime, $cutOffDateTime, $excludedDropOffDays)
    {
        $isInCutOffTime = $currentDateTime < $cutOffDateTime;

        if (!$isInCutOffTime) {
            $currentDateTime->add(new \DateInterval('P1D'));
        }

        $dayCount = 0;
        while ($this->isHoliday($currentDateTime) || $this->isNonDropOffDay($currentDateTime, $excludedDropOffDays)) {
            // if current date is a date where no package can be handed over, try the next day
            $currentDateTime->add(new \DateInterval('P1D'));
            $dayCount++;

            /** If merchant has a bad configuration eg. all days marked as non dropp off days we need to exit the loop.
             *  Exception is thrown and service will be removed from the array.
            **/
            if ($dayCount === 6) {
                throw new LocalizedException(__('No valid start date.'));
            }
        }

        return $currentDateTime;
    }

    /**
     * Check if given date is a german holiday.
     *
     * @param \DateTime $dateTime
     * @return bool
     */
    private function isHoliday($dateTime)
    {
        $year = $dateTime->format('Y');
        $locale = $this->localeResolverFactory->create()->getLocale();
        try {
            $holidayProvider = Yasumi::create('Germany', $year, $locale);
        } catch (\Exception $e) {
            return false;
        }

        return $holidayProvider->isHoliday($dateTime);
    }

    /**
     * @param \DateTime $dateTime
     * @param string[] $excludedDropOffDays
     * @return bool
     */
    private function isNonDropOffDay($dateTime, $excludedDropOffDays): bool
    {
        $weekDay = $dateTime->format('N');
        return in_array($weekDay, $excludedDropOffDays) || $weekDay === self::WEEKDAY_SUNDAY;
    }
}
