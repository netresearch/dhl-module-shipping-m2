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

use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentInterface as Shipment;
use Magento\Shipping\Model\Order\TrackFactory;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator as CoreLabelGenerator;

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
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var RequestBuilderInterface
     */
    private $requestBuilder;

    /**
     * @param CarrierFactory $carrierFactory
     * @param TrackFactory $trackFactory
     * @param CoreLabelGenerator $labelGenerator
     * @param TransactionFactory $transactionFactory
     * @param RequestBuilderInterface $requestBuilder
     * @internal param RequestFactory $requestFactory
     * @internal param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        TrackFactory $trackFactory,
        CoreLabelGenerator $labelGenerator,
        TransactionFactory $transactionFactory,
        RequestBuilderInterface $requestBuilder
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->trackFactory = $trackFactory;
        $this->labelGenerator = $labelGenerator;
        $this->transactionFactory = $transactionFactory;
        $this->requestBuilder = $requestBuilder;
    }

    /**
     * @param Shipment $orderShipment
     * @throws LocalizedException
     */
    public function create(Shipment $orderShipment)
    {
        $order = $orderShipment->getOrder();
        $carrier = $this->carrierFactory->create(
            $order->getShippingMethod(true)->getCarrierCode()
        );
        $carrier->setStore($orderShipment->getStoreId());
        if (!$carrier->isShippingLabelsAvailable()) {
            throw new LocalizedException(__('Shipping labels is not available.'));
        }
        $request = $this->requestBuilder->setOrderShipment($orderShipment)->create();
        $response = $carrier->requestToShipment($request);
        if ($response->hasErrors()) {
            throw new LocalizedException(__($response->getErrors()));
        }
        if (!$response->hasInfo()) {
            throw new LocalizedException(__('Response info does not exist.'));
        }
        $labelsContent = [];
        $trackingNumbers = [];
        $info = $response->getInfo();
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
        $this->saveShipment($orderShipment);
    }

    /**
     * @see \Magento\Shipping\Model\Shipping\LabelGenerator::addTrackingNumbersToShipment()
     * @param Shipment $shipment
     * @param array $trackingNumbers
     * @param string $carrierCode
     * @param string $carrierTitle
     *
     * @return void
     */
    private function addTrackingNumbersToShipment(
        Shipment $shipment,
        $trackingNumbers,
        $carrierCode,
        $carrierTitle
    ) {
        foreach ($trackingNumbers as $number) {
            if (is_array($number)) {
                $this->addTrackingNumbersToShipment(
                    $shipment,
                    $number,
                    $carrierCode,
                    $carrierTitle
                );
            } else {
                $shipment->addTrack(
                    $this->trackFactory->create()->setNumber($number)->setCarrierCode($carrierCode)->setTitle(
                        $carrierTitle
                    )
                );
            }
        }
    }

    /**
     * @param Shipment $orderShipment
     */
    private function saveShipment(Shipment $orderShipment)
    {
        $transaction = $this->transactionFactory->create();
        $transaction->addObject($orderShipment)->addObject($orderShipment->getOrder())->save();
    }
}
