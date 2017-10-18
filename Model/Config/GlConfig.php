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

use \Dhl\Shipping\Config\GlConfigInterface;

/**
 * GlApiConfig
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class GlConfig implements GlConfigInterface
{
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
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTH_PASSWORD, $store);
    }

    /**
     * @param mixed $store
     * @return mixed
     */
    public function getAuthToken($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTH_TOKEN, $store);
    }

    /**
     * @param string $token
     * @param mixed $store
     * @return void
     */
    public function saveAuthToken($token, $store = 0)
    {
        $this->configAccessor->saveConfigValue(self::CONFIG_XML_PATH_AUTH_TOKEN, $token, $store);
    }

    /**
     * Obtain Pickup number.
     *
     * @param mixed $store
     * @return string
     */
    public function getPickupAccountNumber($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_PICKUP_NUMBER, $store);
    }

    /**
     * Obtain Distribution Center.
     *
     * @param mixed $store
     * @return string
     */
    public function getDistributionCenter($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DISTRIBUTION_CENTER, $store);
    }

    /**
     * Obtain preferred PDF page size.
     *
     * @param mixed $store
     * @return string
     */
    public function getPageSize($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_PAGE_SIZE, $store);
    }

    /**
     * Obtain preferred label size on the printed PDF.
     *
     * @param mixed $store
     * @return string
     */
    public function getLabelSize($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_LABEL_SIZE, $store);
    }

    /**
     * Obtain preferred page layout (number of labels per page).
     *
     * @param mixed $store
     * @return string
     */
    public function getPageLayout($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_PAGE_LAYOUT, $store);
    }

    /**
     * Obtain merchants customer prefix
     *
     * @param mixed $store
     * @return string
     */
    public function getCustomerPrefix($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_CUSTOMER_PREFIX, $store);
    }

    /**
     * Get consignment number
     *
     * @param mixed $store
     * @return string
     */
    public function getConsignmentNumber($store = null)
    {
        $prefix = 8;
        $suffix = (int)$this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_CONSIGNMENT_SUFFIX, $store);

        $consignmentNumber = sprintf('%s%s', $prefix, str_pad($suffix, 10, '0', STR_PAD_LEFT));

        return $consignmentNumber;
    }

    /**
     * Increment consignment number
     *
     * @return void
     */
    public function incrementConsignmentNumber()
    {
        $suffix = (int)$this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_CONSIGNMENT_SUFFIX);
        $this->configAccessor->saveConfigValue(
            self::CONFIG_XML_PATH_CONSIGNMENT_SUFFIX,
            $suffix + 1
        );
    }
}
