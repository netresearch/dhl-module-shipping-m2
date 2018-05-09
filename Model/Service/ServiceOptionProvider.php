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
        //todo(nr): once holiday package is added, we need to calculate the next 5 working days -> see: versenden-m1
        return [1, 2, 3, 4, 5,];
    }

    public function getPreferredTimeOptions()
    {
        //todo(nr): return time -> see: versenden-m1
        return [1, 2, 3, 4, 5];
    }

    public function getVisualCheckOfAgeOptions()
    {
        return [
            VisualCheckOfAgeOptions::OPTION_A16 => VisualCheckOfAgeOptions::OPTION_A16,
            VisualCheckOfAgeOptions::OPTION_A18 => VisualCheckOfAgeOptions::OPTION_A18
        ];
    }
}


