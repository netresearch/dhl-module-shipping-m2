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

use Dhl\Shipping\Config\BcsConfigInterface;

/**
 * BcsConfig
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class BcsConfig implements BcsConfigInterface
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
     * BcsApiConfig constructor.
     *
     * @param ConfigAccessorInterface $configAccessor
     * @param ModuleConfigInterface   $moduleConfig
     */
    public function __construct(
        ConfigAccessorInterface $configAccessor,
        ModuleConfigInterface $moduleConfig
    ) {
        $this->configAccessor = $configAccessor;
        $this->moduleConfig   = $moduleConfig;
    }

    /**
     * Obtain API endpoint.
     *
     * @param mixed $store
     *
     * @return string | null
     */
    public function getApiEndpoint($store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_ENDPOINT, $store);
        }

        return null;
    }

    /**
     * Obtain auth credentials: username.
     *
     * @param mixed $store
     *
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
     *
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
     * Obtain DHL Business Customer Shipping contract data: username.
     *
     * Note: Webservice login fails if merchant registered a username with upper
     * case characters. The same username converted to lower case is accepted.
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getAccountUser($store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            $user = $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_ACCOUNT_USER, $store);
        } else {
            $user = $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_ACCOUNT_USER, $store);
        }

        return strtolower($user);
    }

    /**
     * Obtain DHL Business Customer Shipping contract data: signature.
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getAccountSignature($store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_ACCOUNT_SIGNATURE, $store);
        }

        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_ACCOUNT_SIGNATURE, $store);
    }

    /**
     * Obtain DHL Business Customer Shipping contract data: ekp.
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getAccountEkp($store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_ACCOUNT_EKP, $store);
        }

        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_ACCOUNT_EKP, $store);
    }

    /**
     * Obtain DHL Business Customer Shipping contract data: participation numbers.
     *
     * @param mixed  $store
     *
     * @return string[]
     */
    public function getAccountParticipations($store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            $participations = $this->configAccessor->getConfigValue(
                self::CONFIG_XML_PATH_SANDBOX_ACCOUNT_PARTICIPATIONS,
                $store
            );
        } else {
            $participations = $this->configAccessor->getConfigValue(
                self::CONFIG_XML_PATH_ACCOUNT_PARTICIPATIONS,
                $store
            );
        }

        return array_column($participations, 'participation', 'procedure');
    }

    /**
     * Obtain DHL Business Customer Shipping contract data: participation number for a given procedure.
     *
     * @param string $procedure
     * @param mixed  $store
     *
     * @return string
     */
    public function getAccountParticipation($procedure, $store = null)
    {
        if ($this->moduleConfig->isSandboxModeEnabled($store)) {
            $participations = $this->configAccessor->getConfigValue(
                self::CONFIG_XML_PATH_SANDBOX_ACCOUNT_PARTICIPATIONS,
                $store
            );
        } else {
            $participations = $this->configAccessor->getConfigValue(
                self::CONFIG_XML_PATH_ACCOUNT_PARTICIPATIONS,
                $store
            );
        }
        $participations = array_column($participations, 'participation', 'procedure');
        return isset($participations[$procedure]) ? $participations[$procedure] : '';
    }

    /**
     * @param mixed $store
     *
     * @return string
     */
    public function getBankDataAccountOwner($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_BANKDATA_ACCOUNT_OWNER, $store);
    }

    /**
     * @param mixed $store
     *
     * @return string
     */
    public function getBankDataBankName($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_BANKDATA_BANKNAME, $store);
    }

    /**
     * @param mixed $store
     *
     * @return string
     */
    public function getBankDataIban($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_BANKDATA_IBAN, $store);
    }

    /**
     * @param mixed $store
     *
     * @return string
     */
    public function getBankDataBic($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_BANKDATA_BIC, $store);
    }

    /**
     * @param mixed $store
     *
     * @return string[]
     */
    public function getBankDataNote($store = null)
    {
        return [
            $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_BANKDATA_NOTE1, $store),
            $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_BANKDATA_NOTE2, $store),
        ];
    }

    /**
     * @param mixed $store
     *
     * @return string
     */
    public function getBankDataAccountReference($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_BANKDATA_ACCOUNT_REFERENCE, $store);
    }

    /**
     * Obtain name of shipper (first name part)
     *
     * @deprecated Shipment request uses name of currently logged in admin
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\User\Model\User::getName()
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperName($store = null)
    {
        return null;
    }

    /**
     * Obtain shipper company name (second name part)
     *
     * @deprecated Shipment request uses config general/store_information/name
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\Store\Model\Information::XML_PATH_STORE_INFO_NAME
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperCompany($store = null)
    {
        return null;
    }

    /**
     * Obtain shipper company name (third name part)
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperCompanyAddition($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SHIPPER_CONTACT_COMPANYADDITION, $store);
    }

    /**
     * @deprecated Shipment request uses config general/store_information/name
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\Store\Model\Information::XML_PATH_STORE_INFO_PHONE
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperPhone($store = null)
    {
        return null;
    }

    /**
     * @deprecated Shipment request uses email of currently logged in admin
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\User\Model\User::getEmail()
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperEmail($store = null)
    {
        return null;
    }

    /**
     * @deprecated Shipment request uses config shipping/origin/street_line1
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ADDRESS1
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperStreet($store = null)
    {
        return null;
    }

    /**
     * @deprecated Shipment request uses config shipping/origin/street_line1
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ADDRESS1
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperStreetNumber($store = null)
    {
        return null;
    }

    /**
     * @deprecated Shipment request uses config shipping/origin/postcode
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperPostalCode($store = null)
    {
        return null;
    }

    /**
     * @deprecated Shipment request uses config shipping/origin/city
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperCity($store = null)
    {
        return null;
    }

    /**
     * @deprecated Shipment request uses config shipping/origin/region_id
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperRegion($store = null)
    {
        return null;
    }

    /**
     * @deprecated Shipment request uses config shipping/origin/country_id
     * @see        \Magento\Shipping\Model\Shipping\Labels::requestToShipment()
     * @see        \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getShipperCountryISOCode($store = null)
    {
        return null;
    }

    /**
     * @param mixed $store
     *
     * @return string
     */
    public function getDispatchingInformation($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SHIPPER_CONTACT_DISPATCHINFO, $store);
    }
}
