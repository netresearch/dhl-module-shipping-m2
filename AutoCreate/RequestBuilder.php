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
 * @package   Dhl\Shipping\AutoCreate
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\AutoCreate;

use Dhl\Shipping\Helper\ProductData as Helper;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\ApiType;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Config\ServiceConfigInterface;
use Dhl\Shipping\Service\Filter\EnabledFilter;
use Dhl\Shipping\Util\ExportType;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Shipping\Model\Shipment\Request;
use Magento\Shipping\Model\Shipment\RequestFactory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class RequestBuilder
 *
 * Encapsulates building a ShipmentRequest object through injected config and a given order shipment
 *
 * @package  Dhl\Shipping\AutoCreate
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class RequestBuilder implements RequestBuilderInterface
{
    const ORDER_SHIPMENT = 'order_shipment';

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var ServiceConfigInterface
     */
    private $serviceConfig;

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

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var mixed[]
     */
    private $data = [];

    /**
     * RequestBuilder constructor.
     * @param ModuleConfigInterface $moduleConfig
     * @param ServiceConfigInterface $serviceConfig
     * @param RequestFactory $shipmentRequestFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RegionFactory $regionFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param \Magento\Shipping\Model\CarrierFactory $carrierFactory
     * @param Helper $helper
     */
    public function __construct(
        ModuleConfigInterface $moduleConfig,
        ServiceConfigInterface $serviceConfig,
        RequestFactory $shipmentRequestFactory,
        ScopeConfigInterface $scopeConfig,
        RegionFactory $regionFactory,
        DataObjectFactory $dataObjectFactory,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        Helper $helper
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->serviceConfig = $serviceConfig;
        $this->shipmentRequestFactory = $shipmentRequestFactory;
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->carrierFactory = $carrierFactory;
        $this->helper = $helper;
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
        $shipmentRequest->setShippingMethod($shippingMethod->getData('method'));
        $shipmentRequest->setPackageWeight($order->getWeight());
        $shipmentRequest->setBaseCurrencyCode($baseCurrencyCode);
        $shipmentRequest->setStoreId($orderShipment->getStoreId());
        $this->addShipperData($shipmentRequest);
        $this->addReceiverData($shipmentRequest);
        $this->preparePackageData($shipmentRequest);

        $this->data = [];
        return $shipmentRequest;
    }

    /**
     * @param Request $shipmentRequest
     * @throws LocalizedException
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
     * @throws LocalizedException
     */
    private function addReceiverData(Request $shipmentRequest)
    {
        $address = $shipmentRequest->getOrderShipment()->getShippingAddress();
        $personName = trim($address->getFirstname() . ' ' . $address->getLastname());
        $addressStreet = trim($address->getStreetLine(1) . ' ' . $address->getStreetLine(2));
        $region = $address->getRegionCode() ? $address->getRegionCode() : $address->getRegion();

        $shipmentRequest->setRecipientContactPersonName($personName);
        $shipmentRequest->setRecipientContactPersonFirstName($address->getFirstname());
        $shipmentRequest->setRecipientContactPersonLastName($address->getLastname());
        $shipmentRequest->setRecipientContactCompanyName($address->getCompany());
        $shipmentRequest->setRecipientContactPhoneNumber($address->getTelephone());
        $shipmentRequest->setRecipientEmail($address->getEmail());
        $shipmentRequest->setRecipientAddressStreet($addressStreet);
        $shipmentRequest->setRecipientAddressStreet1($address->getStreetLine(1));
        $shipmentRequest->setRecipientAddressStreet2($address->getStreetLine(2));
        $shipmentRequest->setRecipientAddressCity($address->getCity());
        $shipmentRequest->setRecipientAddressStateOrProvinceCode($region);
        $shipmentRequest->setRecipientAddressRegionCode($address->getRegionCode());
        $shipmentRequest->setRecipientAddressPostalCode($address->getPostcode());
        $shipmentRequest->setRecipientAddressCountryCode($address->getCountryId());
    }

    /**
     * @param Request $shipmentRequest
     */
    private function preparePackageData(Request $shipmentRequest)
    {
        $storeId = $shipmentRequest->getOrderShipment()->getStoreId();
        $apiType = $this->moduleConfig->getApiType($storeId);
        $shipperCountry = $this->moduleConfig->getShipperCountry($storeId);
        $destCountryId = $shipmentRequest->getOrderShipment()->getShippingAddress()->getCountryId();
        $isCrossBorder = $this->moduleConfig->isCrossBorderRoute($destCountryId, $storeId);

        $totalWeight = 0;
        $package = [
            'params' => [],
            'items' => [],
        ];

        $productIds = [];
        $orderItemIds = [];
        /** @var \Magento\Sales\Model\Order\Shipment\Item $item */
        foreach ($shipmentRequest->getOrderShipment()->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy(true)) {
                continue;
            }

            $totalWeight += $item->getWeight();

            if ($isCrossBorder) {
                $itemData = $item->toArray(
                    [
                        'qty',
                        'customs_value',
                        'price',
                        'name',
                        'weight',
                        'product_id',
                        'order_item_id',
                        'customs_item_description',
                        'item_origin_country',
                        'tariff_number',
                        'sku'
                    ]
                );
                $itemData['customs_value'] = $itemData['price'];
                $itemData['item_origin_country'] = $shipperCountry;
                $itemData['customsDetails'] = [
                    'itemDescription' => $itemData['name'],
                    'descriptionExport' => '',
                    'descriptionImport' => '',
                    'countryOfOrgin' => $itemData['item_origin_country'],
                    'hsCode' => $itemData['tariff_number'],
                    'packageQuantitiy' => $itemData['qty'],
                    'itemValue' => $itemData['customs_value'],
                    'skuNumer' => $itemData['sku']
                ];
                $itemData['customs_item_description'] = $itemData['name'];
            } else {
                $itemData = $item->toArray(['qty', 'price', 'name', 'weight', 'product_id', 'order_item_id']);
            }
            $package['items'][$item->getOrderItemId()] = $itemData;

            $productIds[] = $item->getProductId();
            $orderItemIds[$item->getOrderItemId()] = $item->getProductId();
        }

        $carrierCode = $shipmentRequest
            ->getOrderShipment()
            ->getOrder()
            ->getShippingMethod(true)
            ->getData('carrier_code');

        $carrier = $this->carrierFactory->create($carrierCode, $storeId);

        $params = $this->dataObjectFactory->create([
            'data' => [
                'country_shipper' => $shipperCountry,
                'country_recipient' => $destCountryId,
            ]
        ]);

        $container = current(array_keys($carrier->getContainerTypes($params)));

        $enabledFilter = EnabledFilter::create();
        $serviceCollection = $this->serviceConfig
            ->getServices($storeId)
            ->filter($enabledFilter);

        $weightUnit = $this->scopeConfig->getValue(
            Data::XML_PATH_WEIGHT_UNIT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $weightUnit = (strtoupper($weightUnit) === \Zend_Measure_Weight::LBS)
            ? \Zend_Measure_Weight::POUND
            : \Zend_Measure_Weight::KILOGRAM;
        $dimensionUnit = (strtoupper($weightUnit) === \Zend_Measure_Weight::LBS)
            ? \Zend_Measure_Length::INCH
            : \Zend_Measure_Length::CENTIMETER;

        if ($isCrossBorder) {
            $defaultTot = $this->moduleConfig->getTermsOfTrade($storeId);
            $productData = $this->helper->getProductData($productIds, $storeId);

            if ($apiType == ApiType::API_TYPE_BCS) {
                $defaultAdditionFee = $this->moduleConfig->getDefaultAdditionalFee($storeId);
                $defaultPoc = $this->moduleConfig->getDefaultPlaceOfCommital($storeId);
                $contentType = $this->moduleConfig->getDefaultExportContentType($storeId);
                if ($contentType == ExportType::TYPE_OTHER) {
                    $contentTypeOther = $this->moduleConfig->getDefaultExportContentTypeExplanation($storeId);
                }
            }

            foreach ($orderItemIds as $itemId => $productId) {
                $package['items'][$itemId]['customs_item_description'] = $package['items'][$itemId]['name'];

                if (!isset($productData[$productId]['dhl_export_description'])) {
                    $productData[$productId]['dhl_export_description'] = $package['items'][$itemId]['name'];
                }

                $package['items'][$itemId]['tariff_number'] =
                    isset($productData[$productId]['dhl_tariff_number']) ?
                        $productData[$productId]['dhl_tariff_number'] :
                        '';
            }
            $exportDescription = '';
            foreach ($productData as $data) {
                $exportDescription .= $data['dhl_export_description'];
            }

            $exportDescription = !empty($exportDescription) ? substr($exportDescription, 0, 50) : '';

            $customsParams = [];
            if (isset($defaultTot)) {
                $customsParams['terms_of_trade'] = $defaultTot;
            }
            if (isset($defaultAdditionFee)) {
                $customsParams['additional_fee'] = $defaultAdditionFee;
            }
            if (isset($defaultPoc)) {
                $customsParams['place_of_commital'] = $defaultPoc;
            }

            $customsParams['export_description'] = $exportDescription;
        }

        $package['params']['container'] = (string)$container;
        $package['params']['weight'] = $totalWeight;
        $package['params']['length'] = '';
        $package['params']['width'] = '';
        $package['params']['height'] = '';
        $package['params']['weight_units'] = $weightUnit;
        $package['params']['dimension_units'] = $dimensionUnit;
        $package['params']['content_type'] = isset($contentType) ? $contentType : '';
        $package['params']['content_type_other'] = isset($contentTypeOther) ? $contentTypeOther : '';
        $package['params']['services'] = $serviceCollection->getConfiguration();

        if (isset($customsParams)) {
            $package['params']['customs'] = $customsParams;
        }

        $packages = [1 => $package];
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
