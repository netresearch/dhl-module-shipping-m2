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
use Dhl\Shipping\Model\ConfigProvider;

/**
 * ConfigProviderPlugin
 *
 * @package  Dhl\Shipping\Plugin
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ConfigProviderPlugin
{
    /**
     * @var ServiceChargeConfig
     */
    private $config;

    /**
     * ConfigProviderPlugin constructor.
     *
     * @param ServiceChargeConfig $config
     */
    public function __construct(ServiceChargeConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param ConfigProvider $subject
     * @param $result
     * @return string[][]
     */
    public function afterGetConfig(ConfigProvider $subject, $result)
    {
        $result['dhl_service_block_after'] = [$this->config->getCombinedChargeText()];

        return $result;
    }
}
