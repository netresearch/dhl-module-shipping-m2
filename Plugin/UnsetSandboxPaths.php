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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2019 Netresearch GmbH & Co. KG
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.netresearch.de/
 */
namespace Dhl\Shipping\Plugin;

use Magento\Config\App\Config\Source\DumpConfigSourceAggregated;

/**
 * UnsetSandboxPaths
 *
 * Sandbox config defaults are static values distributed between environments
 * via config.xml file. There is no need to dump them to the config.php or
 * env.php files. Doing so causes issues when importing them as the necessary
 * backend model is not declared in system.xml
 *
 * @package Dhl\Shipping\Plugin
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.netresearch.de/
 */
class UnsetSandboxPaths
{
    /**
     * Prevent `bcs_sandbox_*` settings from being dumped on `app:config:dump` command.
     *
     * @param DumpConfigSourceAggregated $subject
     * @param string[][][][] $result
     * @return string[][][][]
     */
    public function afterGet(DumpConfigSourceAggregated $subject, $result)
    {
        unset($result['default']['carriers']['dhlshipping']['bcs_sandbox_account_ekp']);
        unset($result['default']['carriers']['dhlshipping']['bcs_sandbox_account_participations']);
        unset($result['default']['carriers']['dhlshipping']['bcs_sandbox_account_signature']);
        unset($result['default']['carriers']['dhlshipping']['bcs_sandbox_account_user']);
        unset($result['default']['carriers']['dhlshipping']['bcs_sandbox_api_auth_password']);
        unset($result['default']['carriers']['dhlshipping']['bcs_sandbox_api_auth_username']);
        unset($result['default']['carriers']['dhlshipping']['bcs_sandbox_api_endpoint']);

        return $result;
    }
}
