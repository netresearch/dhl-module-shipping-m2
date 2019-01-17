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
 * @package   Dhl\Shipping\Plugin
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Plugin;

use Dhl\Shipping\Model\Config\ServiceChargeConfig;
use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Model\Service\ServiceConfig;
use Dhl\Shipping\Service\Bcs\PreferredDay;
use Dhl\Shipping\Service\Bcs\PreferredTime;

/**
 * ServiceConfigProviderPlugin
 *
 * @package  Dhl\Shipping\Plugin
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceConfigProviderPlugin
{
    /**
     * @var ServiceChargeConfig
     */
    private $config;

    /**
     * ServiceConfigProviderPlugin constructor.
     *
     * @param ServiceChargeConfig $config
     */
    public function __construct(ServiceChargeConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param ServiceConfig $subject
     * @param string[][] $result
     * @return string
     */
    public function afterGetServiceSettings(ServiceConfig $subject, $result)
    {
        $prefDay = $result[PreferredDay::CODE];
        $prefTime = $result[PreferredTime::CODE];

        $prefDay[ServiceSettingsInterface::INFO_TEXT] = $this->config->getPrefDayHandlingChargeText();
        $prefTime[ServiceSettingsInterface::INFO_TEXT] = $this->config->getPrefTimeHandlingChargeText();

        $result[PreferredDay::CODE] = $prefDay;
        $result[PreferredTime::CODE] = $prefTime;

        return $result;
    }
}
