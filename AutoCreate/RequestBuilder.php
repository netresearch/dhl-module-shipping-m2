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

namespace Dhl\Shipping\AutoCreate;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Shipment;
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
    private $shipmentRequestFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /** @var  DataObjectFactory */
    private $dataObjectFactory;

    /**
     * @var mixed[]
     */
    private $data = [];

    /**
     * RequestBuilder constructor.
     * @param RequestFactory $shipmentRequestFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionFactory $regionFactory
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        RequestFactory $shipmentRequestFactory,
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->shipmentRequestFactory = $shipmentRequestFactory;
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @param ShipmentInterface $orderShipment
     * @return void
     */
    public function setOrderShipment(ShipmentInterface $orderShipment)
    {
        $this->data[self::ORDER_SHIPMENT] = $orderShipment;
    }

    /**
     * @return Request
     * @throws LocalizedException
     */
    public function create()
    {
        if (!isset($this->data[self::ORDER_SHIPMENT])) {
            throw new LocalizedException(__('No shipment has been set yet.'));
        }

        /** @var ShipmentInterface|\Magento\Sales\Model\Order\Shipment $orderShipment */
        $orderShipment = $this->data[self::ORDER_SHIPMENT];

        $baseCurrencyCode = $orderShipment->getOrder()->getBaseCurrencyCode();
        $order = $orderShipment->getOrder();
        $shippingMethod = $order->getShippingMethod(true);

        $shipmentRequest = $this->shipmentRequestFactory->create();

        $shipmentRequest->setOrderShipment($orderShipment);
        $this->addShipperData($shipmentRequest);
        $this->addReceiverData($shipmentRequest);
        $this->preparePackageData($shipmentRequest);
        $shipmentRequest->setShippingMethod($shippingMethod->getMethod());
        $shipmentRequest->setPackageWeight($order->getWeight());
        $shipmentRequest->setBaseCurrencyCode($baseCurrencyCode);
        $shipmentRequest->setStoreId($orderShipment->getStoreId());

        $this->data = [];
        return $shipmentRequest;
    }

    /**
     * @param Request $shipmentRequest
     */
    private function addShipperData(Request $shipmentRequest)
    {
        $storeId = $shipmentRequest->getOrderShipment()->getStoreId();

        $originStreet = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS1,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $originStreet2 = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_ADDRESS2,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $storeInfo = (array)$this->scopeConfig->getValue(
            'general/store_information',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $storeInfo = $this->dataObjectFactory->create(['data' => $storeInfo]);

        $shipperRegionCode = $this->scopeConfig->getValue(
            Shipment::XML_PATH_STORE_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if (is_numeric($shipperRegionCode)) {
            $shipperRegionCode = $this->regionFactory->create()->load($shipperRegionCode)->getCode();
        }

        $storeContact = (array)$this->scopeConfig->getValue('trans_email/ident_sales');
        $storeContact = $this->dataObjectFactory->create(['data' => $storeContact]);

        $shipmentRequest->setShipperContactPersonName($storeContact->getName());
        $shipmentRequest->setShipperContactPersonFirstName('');
        $shipmentRequest->setShipperContactPersonLastName('');
        $shipmentRequest->setShipperContactCompanyName($storeInfo->getName());
        $shipmentRequest->setShipperContactPhoneNumber($storeInfo->getPhone());
        $shipmentRequest->setShipperEmail($storeContact->getEmail());
        $shipmentRequest->setShipperAddressStreet(trim($originStreet . ' ' . $originStreet2));
        $shipmentRequest->setShipperAddressStreet1($originStreet);
        $shipmentRequest->setShipperAddressStreet2($originStreet2);
        $shipmentRequest->setShipperAddressCity(
            $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_CITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        $shipmentRequest->setShipperAddressStateOrProvinceCode($shipperRegionCode);
        $shipmentRequest->setShipperAddressPostalCode(
            $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_ZIP,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
        $shipmentRequest->setShipperAddressCountryCode(
            $this->scopeConfig->getValue(
                Shipment::XML_PATH_STORE_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
        );
    }

    /**
     * @param Request $shipmentRequest
     */
    private function addReceiverData(Request $shipmentRequest)
    {
        $address = $shipmentRequest->getOrderShipment()->getShippingAddress();
        $shipmentRequest->setRecipientContactPersonName(trim($address->getFirstname() . ' ' . $address->getLastname()));
        $shipmentRequest->setRecipientContactPersonFirstName($address->getFirstname());
        $shipmentRequest->setRecipientContactPersonLastName($address->getLastname());
        $shipmentRequest->setRecipientContactCompanyName($address->getCompany());
        $shipmentRequest->setRecipientContactPhoneNumber($address->getTelephone());
        $shipmentRequest->setRecipientEmail($address->getEmail());
        $shipmentRequest->setRecipientAddressStreet(trim($address->getStreetLine(1) . ' ' . $address->getStreetLine(2)));
        $shipmentRequest->setRecipientAddressStreet1($address->getStreetLine(1));
        $shipmentRequest->setRecipientAddressStreet2($address->getStreetLine(2));
        $shipmentRequest->setRecipientAddressCity($address->getCity());
        $shipmentRequest->setRecipientAddressStateOrProvinceCode($address->getRegionCode() ?: $address->getRegion());
        $shipmentRequest->setRecipientAddressRegionCode($address->getRegionCode());
        $shipmentRequest->setRecipientAddressPostalCode($address->getPostcode());
        $shipmentRequest->setRecipientAddressCountryCode($address->getCountryId());
    }

    /**
     * @param Request $shipmentRequest
     */
    private function preparePackageData(Request $shipmentRequest)
    {
        /** @var Shipment\Package[] $packages */
        $packages = $shipmentRequest->getOrderShipment()->getPackages();
        $weightUnit = $this->scopeConfig->getValue(
            Data::XML_PATH_WEIGHT_UNIT,
            ScopeInterface::SCOPE_STORE,
            $shipmentRequest->getOrderShipment()->getStoreId()
        );
        $weightUnit = (strtoupper($weightUnit) === \Zend_Measure_Weight::LBS)
            ? \Zend_Measure_Weight::POUND
            : \Zend_Measure_Weight::KILOGRAM;

        $setPackageItems = function (&$package, $index) use ($weightUnit) {
            $items = [];
            $weight = 0;

            /** @var Shipment\Item $item */
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
        $shipmentRequest->setData('packages', $packages);
        $shipmentRequest->getOrderShipment()->setPackages($packages);
    }

    /**
     * Return data Object data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
