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
 * @category  Dhl
 * @package   Dhl\Shipping\Model\AutoCreate
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\AutoCreate;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;

class Shipper extends DataObject
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Shipper constructor.
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($data);
        $this->scopeConfig = $scopeConfig;
    }

    public function generateFromStoreConfig($storeId = null)
    {
        $originStreet = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS1,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $originStreet2 = $this->_scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS2,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $storeInfo = new DataObject(
            (array)$this->scopeConfig->getValue(
                'general/store_information',
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );

        $shipperRegionCode = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = $this->_regionFactory->create()
                                                      ->load($shipperRegionCode)
                                                      ->getCode();
        }
        // @TODO replace store admin values with some config settings
        $this->setShipperContactPersonName($storeAdmin->getName());
        $this->setShipperContactPersonFirstName($storeAdmin->getFirstname());
        $this->setShipperContactPersonLastName($storeAdmin->getLastname());
        $this->setShipperContactCompanyName($storeInfo->getName());
        $this->setShipperContactPhoneNumber($storeInfo->getPhone());
        $this->setShipperEmail($storeAdmin->getEmail());
        $this->setShipperAddressStreet(trim($originStreet . ' ' . $originStreet2));
        $this->setShipperAddressStreet1($originStreet);
        $this->setShipperAddressStreet2($originStreet2);
        $this->setShipperAddressCity(
            $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        $this->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $this->setShipperAddressPostalCode(
            $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        $this->setShipperAddressCountryCode(
            $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

}