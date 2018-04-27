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

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Order\TrackFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator as CoreLabelGenerator;

/**
 * Class LabelGenerator
 *
 * Encapsulates sending creating and handling shipment requests for the carrier headless.
 * Will automatically save the generated Labels trough the corresponding Magento hooks.
 *
 * @see \Magento\Shipping\Model\Shipping\LabelGenerator
 *
 * @package  Dhl\Shipping\AutoCreate
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 */
class LabelGenerator implements LabelGeneratorInterface
{
    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var TrackFactory
     */
    private $trackFactory;

    /**
     * @var CoreLabelGenerator
     */
    private $labelGenerator;

    /**
     * @var RequestBuilderInterface
     */
    private $shipmentRequestBuilder;

    /**
     * @param CarrierFactory $carrierFactory
     * @param TrackFactory $trackFactory
     * @param CoreLabelGenerator $labelGenerator
     * @param RequestBuilderInterface $requestBuilder
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        TrackFactory $trackFactory,
        CoreLabelGenerator $labelGenerator,
        RequestBuilderInterface $requestBuilder
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->trackFactory = $trackFactory;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentRequestBuilder = $requestBuilder;
    }

    /**
     * @inheritdoc
     * @param ShipmentInterface|\Magento\Sales\Model\Order\Shipment $orderShipment
     * @throws LocalizedException
     * @throws \Zend_Pdf_Exception
     */
    public function create(ShipmentInterface $orderShipment)
    {
        $shippingMethod = $orderShipment->getOrder()->getShippingMethod(true);
        $carrier = $this->carrierFactory->create(
            $shippingMethod->getData('carrier_code'),
            $orderShipment->getStoreId()
        );

        if (!$carrier->isShippingLabelsAvailable()) {
            throw new LocalizedException(__('Shipping labels is not available.'));
        }

        $this->shipmentRequestBuilder->setOrderShipment($orderShipment);
        $shipmentRequest = $this->shipmentRequestBuilder->create();

        $response = $carrier->requestToShipment($shipmentRequest);
        if ($response->hasData('errors')) {
            throw new LocalizedException(__($response->getData('errors')));
        }
        if (!$response->hasData('info')) {
            throw new LocalizedException(__('Response info does not exist.'));
        }

        $labelsContent = [];
        $trackingNumbers = [];
        $info = $response->getData('info');
        foreach ($info as $inf) {
            if (!empty($inf['tracking_number'])) {
                $trackingNumbers[] = $inf['tracking_number'];
            }
            if (!empty($inf['label_content'])) {
                $labelsContent[] = $inf['label_content'];
            }
        }
        $outputPdf = $this->labelGenerator->combineLabelsPdf($labelsContent);
        $orderShipment->setShippingLabel($outputPdf->render());
        $carrierCode = $carrier->getCarrierCode();
        $carrierTitle = $carrier->getConfigData('title');
        if (!empty($trackingNumbers)) {
            $this->addTrackingNumbersToShipment(
                $orderShipment,
                $trackingNumbers,
                $carrierCode,
                $carrierTitle
            );
        }
    }

    /**
     * @see \Magento\Shipping\Model\Shipping\LabelGenerator::addTrackingNumbersToShipment()
     * @param ShipmentInterface|\Magento\Sales\Model\Order\Shipment $shipment
     * @param array $trackingNumbers
     * @param string $carrierCode
     * @param string $carrierTitle
     *
     * @return void
     */
    private function addTrackingNumbersToShipment(
        ShipmentInterface $shipment,
        $trackingNumbers,
        $carrierCode,
        $carrierTitle
    ) {
        foreach ($trackingNumbers as $number) {
            if (is_array($number)) {
                $this->addTrackingNumbersToShipment($shipment, $number, $carrierCode, $carrierTitle);
            } else {
                $track = $this->trackFactory->create();
                $track->setNumber($number);
                $track->setCarrierCode($carrierCode);
                $track->setTitle($carrierTitle);
                $shipment->addTrack($track);
            }
        }
    }
}
