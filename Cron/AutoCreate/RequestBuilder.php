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
 * @package   Dhl\Shipping\Cron\AutoCreate
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Cron\AutoCreate;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Shipping\Model\Shipment\RequestFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Directory\Helper\Data;

/**
 * Class RequestBuilder
 *
 * Encapsulates building a ShipmentRequest object through injected config and a given order shipment
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class RequestBuilder implements RequestBuilderInterface
{
    const ORDER_SHIPMENT = 'order_shipment';

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var mixed[]
     */
    private $data = [];

    public function __construct(
        RequestFactory $requestFactory,
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory
    ) {
        $this->requestFactory = $requestFactory;
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
    }

    public function setOrderShipment(Order\Shipment $orderShipment)
    {
        $this->data[self::ORDER_SHIPMENT] = $orderShipment;
        return $this;
    }

    public function create()
    {
        if (!isset($this->data[self::ORDER_SHIPMENT]) || !$this->data[self::ORDER_SHIPMENT] instanceof Order\Shipment) {
            throw new LocalizedException(__('No shipment has been set yet.'));
        }
        $orderShipment = $this->data[self::ORDER_SHIPMENT];
        $this->data = [];
        return $this->prepareShipmentRequest($orderShipment);
    }

    /**
     * @param Order\Shipment $orderShipment
     * @return Request
     */
    private function prepareShipmentRequest($orderShipment)
    {
        $baseCurrencyCode = $orderShipment->getOrder()->getBaseCurrencyCode();
        $order = $orderShipment->getOrder();
        $shippingMethod = $order->getShippingMethod(true);
        $request = $this->requestFactory->create();
        $request->setOrderShipment($orderShipment);
        $this->addShipperData(
            $request
        );
        $this->addReceiverData($request);
        $this->preparePackageData($request);
        $request->setShippingMethod($shippingMethod->getMethod());
        $request->setPackageWeight($order->getWeight());
        $request->setBaseCurrencyCode($baseCurrencyCode);
        $request->setStoreId($orderShipment->getStoreId());
        return $request;
    }

    /**
     * @param Request $request
     */
    private function addShipperData(
        $request
    ) {
        $storeId = $request->getOrderShipment()->getStoreId();

        $originStreet = $this->scopeConfig->getValue(
            Order\Shipment::XML_PATH_STORE_ADDRESS1,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $originStreet2 = $this->scopeConfig->getValue(
            Order\Shipment::XML_PATH_STORE_ADDRESS2,
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
            Order\Shipment::XML_PATH_STORE_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = $this->regionFactory->create()->load($shipperRegionCode)->getCode();
        }

        $storeContact = new DataObject(
            (array)$this->scopeConfig->getValue(
                'trans_email/ident_sales'
            )
        );
        $request->setShipperContactPersonName($storeContact->getName());
        $request->setShipperContactPersonFirstName('');
        $request->setShipperContactPersonLastName('');
        $request->setShipperContactCompanyName($storeInfo->getName());
        $request->setShipperContactPhoneNumber($storeInfo->getPhone());
        $request->setShipperEmail($storeContact->getEmail());
        $request->setShipperAddressStreet(trim($originStreet . ' ' . $originStreet2));
        $request->setShipperAddressStreet1($originStreet);
        $request->setShipperAddressStreet2($originStreet2);
        $request->setShipperAddressCity(
            $this->scopeConfig->getValue(
                Order\Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        $request->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $request->setShipperAddressPostalCode(
            $this->scopeConfig->getValue(
                Order\Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        $request->setShipperAddressCountryCode(
            $this->scopeConfig->getValue(
                Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    private function addReceiverData(Request $request)
    {
        $address = $request->getOrderShipment()->getShippingAddress();
        $request->setRecipientContactPersonName(trim($address->getFirstname() . ' ' . $address->getLastname()));
        $request->setRecipientContactPersonFirstName($address->getFirstname());
        $request->setRecipientContactPersonLastName($address->getLastname());
        $request->setRecipientContactCompanyName($address->getCompany());
        $request->setRecipientContactPhoneNumber($address->getTelephone());
        $request->setRecipientEmail($address->getEmail());
        $request->setRecipientAddressStreet(trim($address->getStreetLine(1) . ' ' . $address->getStreetLine(2)));
        $request->setRecipientAddressStreet1($address->getStreetLine(1));
        $request->setRecipientAddressStreet2($address->getStreetLine(2));
        $request->setRecipientAddressCity($address->getCity());
        $request->setRecipientAddressStateOrProvinceCode($address->getRegionCode() ?: $address->getRegion());
        $request->setRecipientAddressRegionCode($address->getRegionCode());
        $request->setRecipientAddressPostalCode($address->getPostcode());
        $request->setRecipientAddressCountryCode($address->getCountryId());
    }

    private function preparePackageData(Request $request)
    {
        /** @var Order\Shipment\Package[] $packages */
        $packages = $request->getOrderShipment()->getPackages();
        $weightUnit = $this->scopeConfig->getValue(
            Data::XML_PATH_WEIGHT_UNIT,
            ScopeInterface::SCOPE_STORE,
            $request->getOrderShipment()->getStoreId()
        );
        $weightUnit = (strtoupper($weightUnit) === \Zend_Measure_Weight::LBS)
            ? \Zend_Measure_Weight::POUND
            : \Zend_Measure_Weight::KILOGRAM;

        $setPackageItems = function (&$package, $index) use ($weightUnit) {
            $items = [];
            $weight = 0;

            /** @var Order\Shipment\Item $item */
            foreach ($package['items'] as $item) {
                $items[] = $item->toArray();
                $item['weight_units'] = $weightUnit;
                $weight += $item->getWeight();
            }

            $package['params']['weight'] = $weight;
            $package['params']['weight_units'] = $weightUnit;
            $package['items'] = $items;
        };

        array_walk($packages, $setPackageItems);

        //TODO(nr): DHLVM2-42: read service configuration from config and attach to package
        $request->setData('packages', $packages);
    }

    public function getData()
    {
        return $this->data;
    }
}
