<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Model\Shipping;

use \Magento\Quote\Model\Quote\Address\RateRequest;
use \Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use \Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Carrier
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Carrier extends AbstractCarrierOnline implements CarrierInterface
{
    const CODE = 'dhlversenden';

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var \Dhl\Versenden\Api\Webservice\GatewayInterface
     */
    private $webserviceGateway;

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
        \Dhl\Versenden\Api\Webservice\GatewayInterface $webserviceGateway,
        array $data = []
    ) {
        $this->_code = self::CODE;

        $this->dataObjectFactory = $dataObjectFactory;
        $this->webserviceGateway = $webserviceGateway;

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
     * The DHL Versenden shipping carrier does not calculate rates.
     *
     * @param RateRequest $request
     * @return null
     */
    public function collectRates(RateRequest $request)
    {
        return null;
    }

    /**
     * The DHL Versenden shipping carrier does not introduce own methods.
     *
     * @return mixed[]
     */
    public function getAllowedMethods()
    {
        return [];
    }

    /**
     * Process ONE package and return label info or errors string.
     * @see AbstractCarrierOnline::requestToShipment()
     * @see \Magento\Shipping\Model\Shipping\LabelGenerator::create()
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $sequenceNumber = sprintf(
            '%s-%s',
            $request->getOrderShipment()->getOrder()->getIncrementId(),
            $request->getData('package_id')
        );

        $result = $this->dataObjectFactory->create();
        $response = $this->webserviceGateway->createLabels([$sequenceNumber => $request]);
        if ($response->getStatus()->isError()) {
            // plain string seems to be expected for errors
            $result->setData('errors', $response->getStatus()->getMessage());
        } else {
            $result->setData(
                [
                    'tracking_number' => $response->getCreatedItem($sequenceNumber)->getTrackingNumber(),
                    'shipping_label_content'   => $response->getCreatedItem($sequenceNumber)->getLabel(),
                ]
            );
        }

        return $result;
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
        $tracking->setData([
            'carrier' => $this->_code,
            'carrier_title' => $this->getConfigData('title'),
            'tracking' => $trackingNumber,
            'url' => 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=' . $trackingNumber
        ]);

        return $tracking;
    }
}
