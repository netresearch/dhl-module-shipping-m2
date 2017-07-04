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

use Dhl\Shipping\Model\AutoCreate\Receiver;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipment\RequestFactory;
use Magento\Shipping\Model\Shipping\LabelGenerator as CoreLabelGenerator;

class LabelGenerator implements LabelGeneratorInterface
{
    /**
     * @var CarrierFactory
     */
    private $carrierFactory;

    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * @var Order\Shipment\TrackFactory
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
     * @param CarrierFactory $carrierFactory
     * @param RequestFactory $requestFactory
     * @param Order\Shipment\TrackFactory $trackFactory
     * @param CoreLabelGenerator $labelGenerator
     * @param TransactionFactory $transactionFactory
     * @internal param LabelsFactory $labelFactory
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        RequestFactory $requestFactory,
        Order\Shipment\TrackFactory $trackFactory,
        CoreLabelGenerator $labelGenerator,
        TransactionFactory $transactionFactory
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->requestFactory = $requestFactory;
        $this->trackFactory = $trackFactory;
        $this->labelGenerator = $labelGenerator;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @param Order\Shipment $orderShipment
     * @throws LocalizedException
     */
    public function create(Order\Shipment $orderShipment)
    {
        $order = $orderShipment->getOrder();
        $carrier = $this->carrierFactory->create(
            $order->getShippingMethod(true)
                  ->getCarrierCode()
        );
        $carrier->setStore($orderShipment->getStoreId());
        if (!$carrier->isShippingLabelsAvailable()) {
            throw new LocalizedException(__('Shipping labels is not available.'));
        }
        $request = $this->prepareShipmentRequest($orderShipment);
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
            if (!empty($inf['tracking_number']) && !empty($inf['label_content'])) {
                $labelsContent[] = $inf['label_content'];
                $trackingNumbers[] = $inf['tracking_number'];
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
     * @param Order\Shipment $shipment
     * @param array $trackingNumbers
     * @param string $carrierCode
     * @param string $carrierTitle
     *
     * @return void
     */
    private function addTrackingNumbersToShipment(
        Order\Shipment $shipment,
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
                    $this->trackFactory->create()
                                       ->setNumber($number)
                                       ->setCarrierCode($carrierCode)
                                       ->setTitle($carrierTitle)
                );
            }
        }
    }

    /**
     * @param Order\Shipment $orderShipment
     */
    private function saveShipment(Order\Shipment $orderShipment)
    {
        $transaction = $this->transactionFactory->create();
        $transaction->addObject($orderShipment)
                    ->addObject($orderShipment->getOrder())
                    ->save();
    }

    /**
     * @param Order\Shipment $orderShipment
     */
    private function prepareShipmentRequest($orderShipment)
    {
        //@TODO finish shipment request preparation
        $baseCurrencyCode = $orderShipment->getOrder()
                                          ->getBaseCurrencyCode();
        $request = $this->requestFactory->create();
        $request->addData(Receiver::fromShippingAddress($orderShipment->getShippingAddress()));
    }
}