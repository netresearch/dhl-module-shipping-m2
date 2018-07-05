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
 * @package   Dhl\Shipping
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Shipping;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Util\ExportTypeInterface;
use Dhl\Shipping\Util\ShippingProductsInterface;
use Dhl\Shipping\Webservice\GatewayInterface;
use Magento\Framework\EntityManager\EventManager;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\Event\ManagerInterface;

/**
 * Carrier
 *
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 *
 * @method \Magento\Store\Model\Store|int getStore()
 */
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    const CODE = 'dhlshipping';

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * @var GatewayInterface
     */
    private $webserviceGateway;

    /**
     * @var ExportTypeInterface
     */
    private $exportTypes;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * @var EventManager;
     */
    private $eventManager;

    /**
     * Carrier constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Xml\Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param LabelGenerator $labelGenerator
     * @param ModuleConfigInterface $config
     * @param ShippingProductsInterface $shippingProducts
     * @param ExportTypeInterface $exportTypes
     * @param GatewayInterface $webserviceGateway
     * @param ManagerInterface $eventManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Xml\Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        LabelGenerator $labelGenerator,
        ModuleConfigInterface $config,
        ShippingProductsInterface $shippingProducts,
        ExportTypeInterface $exportTypes,
        GatewayInterface $webserviceGateway,
        ManagerInterface $eventManager,
        array $data = []
    ) {
        $this->_code = self::CODE;

        $this->dataObjectFactory = $dataObjectFactory;
        $this->config = $config;
        $this->shippingProducts = $shippingProducts;
        $this->webserviceGateway = $webserviceGateway;
        $this->exportTypes = $exportTypes;
        $this->labelGenerator = $labelGenerator;
        $this->eventManager = $eventManager;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     * Obtain the shipping products that match the given route. List might get
     * lengthy, so we move the product that was configured as default to the top.
     *
     * @param string $countryShipper
     * @param string $countryRecipient
     * @param mixed $store
     * @return string[]
     */
    private function getShippingProducts($countryShipper, $countryRecipient, $store)
    {

        // read available codes
        if (!$countryShipper || !$countryRecipient) {
            $codes = $this->shippingProducts->getAllCodes();
        } else {
            $euCountries = $this->config->getEuCountryList();
            $codes = $this->shippingProducts->getApplicableCodes($countryShipper, $countryRecipient, $euCountries);
        }

        // obtain human readable names, combine to array
        $names = array_map(function ($code) {
            return $this->shippingProducts->getProductName($code);
        }, $codes);
        $shippingProducts = array_combine($codes, $names);

        $defaultProduct = $this->config->getDefaultProduct($countryRecipient, $store);
        // move default product to top of the list, if available
        if ($defaultProduct) {
            uksort($shippingProducts, function ($keyA, $keyB) use ($defaultProduct) {
                if ($keyA == $defaultProduct) {
                    return -1;
                }
                if ($keyB == $defaultProduct) {
                    return 1;
                }
                return 0;
            });
        }

        return $shippingProducts;
    }

    /**
     * The DHL Shipping shipping carrier does not calculate rates.
     *
     * @param RateRequest $request
     * @return null
     */
    public function collectRates(RateRequest $request)
    {
        return null;
    }

    /**
     * The DHL Shipping shipping carrier does not introduce own methods.
     *
     * @return mixed[]
     */
    public function getAllowedMethods()
    {
        return [];
    }

    /**
     * Return container types of carrier
     *
     * @param \Magento\Framework\DataObject|null $params
     * @return string[]
     */
    public function getContainerTypes(\Magento\Framework\DataObject $params = null)
    {
        if ($params == null) {
            $countryShipper = '';
            $countryRecipient = '';
            $store = '';
        } else {
            $countryShipper = $params->getData('country_shipper');
            $countryRecipient = $params->getData('country_recipient');
            $store = $this->getStore();
        }

        $products = $this->getShippingProducts($countryShipper, $countryRecipient, $store);

        return $products;
    }

    /**
     * Process ONE package and return label info or errors string.
     * @see AbstractCarrierOnline::requestToShipment()
     * @see \Magento\Shipping\Model\Shipping\LabelGenerator::create()
     *
     * @param \Magento\Shipping\Model\Shipment\Request|\Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @throws \Zend_Pdf_Exception
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $sequenceNumber = sprintf(
            '%s-%s',
            $request->getOrderShipment()->getOrder()->getIncrementId(),
            $request->getData('package_id')
        );
        $orderShipment = $request->getOrderShipment();

        $result = $this->dataObjectFactory->create();
        $eventData = [
            'order' => $orderShipment->getOrder()
        ];
        $response = $this->webserviceGateway->createLabels([$sequenceNumber => $request]);
        if ($response->getStatus()->isError()) {
            // plain string seems to be expected for errors
            $result->setData('errors', $response->getStatus()->getMessage());
            $eventData['errors'] = $response->getStatus()->getMessage();
        } else {
            $createdItems = $response->getCreatedItems();
            $createdItem = current($createdItems);

            $combinedLabel = $this->labelGenerator->combineLabelsPdf(
                $createdItem->getAllLabels()
            )->render();

            $result->setData(
                [
                    'tracking_number' => $createdItem->getTrackingNumber(),
                    'shipping_label_content' => $combinedLabel,
                ]
            );
        }
        $this->eventManager->dispatch('dhlshipping_label_create_after', $eventData);

        return $result;
    }

    /**
     * For multi package shipments. Delete requested shipments if the current shipment
     * request is failed
     *
     * In case one request succeeded and another request failed, Magento will
     * discard the successfully created label. That means, labels created through
     * BCS API must be cancelled.
     *
     * @api
     * @param string[][] $data Arrays of info data with tracking_number and label_content
     * @return bool
     */
    public function rollBack($data)
    {
        $shipmentNumbers = array_map(function (array $info) {
            return $info['tracking_number'];
        }, $data);

        $this->webserviceGateway->cancelLabels($shipmentNumbers);

        return parent::rollBack($data);
    }

    /**
     * Get tracking information. Original return value annotation is misleading.
     *
     * @see \Magento\Shipping\Model\Carrier\AbstractCarrier::isTrackingAvailable()
     * @see \Magento\Shipping\Model\Carrier\AbstractCarrierOnline::getTrackingInfo()
     * @see \Magento\Dhl\Model\Carrier::getTracking()
     * @param string $trackingNumber
     * @return \Magento\Shipping\Model\Tracking\Result\AbstractResult
     */
    public function getTrackingInfo($trackingNumber)
    {
        /** @var \Magento\Shipping\Model\Tracking\Result\Status $tracking */
        $tracking = $this->_trackStatusFactory->create();

        $shippingOrigin = $this->config->getShipperCountry($this->getStore()->getId());

        if (in_array($shippingOrigin, ['DE', 'AT'])) {
            $url = 'https://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=' . $trackingNumber;
        } else {
            $url = 'https://webtrack.dhlglobalmail.com/?trackingnumber=' . $trackingNumber;
        }

        $tracking->setData([
            'carrier' => $this->_code,
            'carrier_title' => $this->getConfigData('title'),
            'tracking' => $trackingNumber,
            'url' => $url,
        ]);

        return $tracking;
    }

    /**
     * @param \Magento\Framework\DataObject $params
     * @return \string[]
     */
    public function getContentTypes(\Magento\Framework\DataObject $params)
    {
        if ($params == null) {
            $countryShipper = '';
            $countryRecipient = '';
        } else {
            $countryShipper = $params->getData('country_shipper');
            $countryRecipient = $params->getData('country_recipient');
        }

        $contentTypes = $this->exportTypes->getApplicableTypes(
            $countryShipper,
            $countryRecipient,
            $this->config->getEuCountryList()
        );
        return $contentTypes;
    }
}
