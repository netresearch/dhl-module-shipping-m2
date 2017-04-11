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
 * @category  Dhl
 * @package   Dhl\Shipping
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Config;

use \Dhl\Shipping\Api\Config\ConfigAccessorInterface;
use \Dhl\Shipping\Api\Config\GlConfigInterface;
use \Dhl\Shipping\Api\Config\ModuleConfigInterface;

/**
 * GlApiConfig
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class GlConfig implements GlConfigInterface
{
    const CONFIG_XML_PATH_PICKUP_NUMBER_SANDBOX     = 'carriers/dhlshipping/gl_pickup_number_sandbox';
    const CONFIG_XML_PATH_PICKUP_NUMBER_PRODUCTION  = 'carriers/dhlshipping/gl_pickup_number_production';

    const CONFIG_XML_PATH_ENDPOINT      = 'carriers/dhlshipping/api_gl_endpoint';
    const CONFIG_XML_PATH_AUTH_USERNAME = 'carriers/dhlshipping/api_gl_auth_username';
    const CONFIG_XML_PATH_AUTH_PASSWORD = 'carriers/dhlshipping/api_gl_auth_password';

    const CONFIG_XML_PATH_SANDBOX_ENDPOINT      = 'carriers/dhlshipping/api_gl_sandbox_endpoint';
    const CONFIG_XML_PATH_SANDBOX_AUTH_USERNAME = 'carriers/dhlshipping/api_gl_sandbox_auth_username';
    const CONFIG_XML_PATH_SANDBOX_AUTH_PASSWORD = 'carriers/dhlshipping/api_gl_sandbox_auth_password';

    /**
     * @var ConfigAccessorInterface
     */
    private $configAccessor;

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * GlApiConfig constructor.
     * @param ConfigAccessorInterface $configAccessor
     * @param ModuleConfigInterface $moduleConfig
     */
    public function __construct(
        ConfigAccessorInterface $configAccessor,
        ModuleConfigInterface $moduleConfig
    ) {
        $this->configAccessor = $configAccessor;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Obtain API endpoint.
     *
     * @param mixed $store
     * @return string
     */
    public function getApiEndpoint($store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_ENDPOINT, $store);
        }

        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_ENDPOINT, $store);
    }

    /**
     * Obtain auth credentials: username.
     *
     * @param mixed $store
     * @return string
     */
    public function getAuthUsername($store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_AUTH_USERNAME, $store);
        }

        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTH_USERNAME, $store);
    }

    /**
     * Obtain auth credentials: password.
     *
     * @param mixed $store
     * @return string
     */
    public function getAuthPassword($store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_AUTH_PASSWORD, $store);
        }

        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTH_PASSWORD, $store);
    }

    /**
     * @param mixed $store
     * @return string
     */
    public function getSandboxPickupAccountNumber($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_PICKUP_NUMBER_SANDBOX, $store);
    }

    /**
     * @param mixed $store
     * @return string
     */
    public function getProductionPickupAccountNumber($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_PICKUP_NUMBER_PRODUCTION, $store);
    }
}
