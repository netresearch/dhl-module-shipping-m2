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

    public function getPreferredDayOptions()
    {
        $options = [];
        $daysToCalculate = 5;
        $year = date('Y');
        $holidayProvider = Yasumi::create('Germany', $year, 'de_DE');

        for ($i = 1; $i <= $daysToCalculate; $i++) {
            $disabled = false;
            $date = date("Y-m-d", time() + 86400 * $i);
            $dateTime = new \DateTime($date);
            $dayOfWeek = date("l", strtotime($date));
            if ($holidayProvider->isHoliday($dateTime) || ($dayOfWeek === 'Sunday')) {
                $disabled = true;
                $daysToCalculate++;
            }

            $options[] = [
                'label' => substr($dayOfWeek, 0, 3),
                'labelValue' => substr($date, -2),
                'value' => $date,
                'disabled' => $disabled
            ];
        }

        return $options;
    }

    public function getPreferredTimeOptions()
    {
        $options = [
            [
                'label' => __('18 - 20'),
                'value' => '18002000'
            ],
            [
                'label' => __('19 - 21'),
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
