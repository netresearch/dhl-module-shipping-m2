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
 * @package   Dhl\Versenden\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Webservice\Adapter;

use \Dhl\Versenden\Api\Data\Webservice\RequestType;
use \Dhl\Versenden\Api\Data\Webservice\ResponseType;
use \Dhl\Versenden\Api\Data\Webservice\ResponseType\Generic\ResponseStatusInterface;
use \Dhl\Versenden\Api\Webservice\Adapter\AdapterChainInterface;
use \Dhl\Versenden\Api\Webservice\Adapter\BcsAdapterInterfaceFactory;
use \Dhl\Versenden\Api\Webservice\Adapter\GlAdapterInterfaceFactory;
use \Dhl\Versenden\Webservice\ResponseType\CreateShipmentResponseCollection;
use \Dhl\Versenden\Webservice\ResponseType\DeleteShipmentResponseCollection;
use \Dhl\Versenden\Webservice\ResponseType\Generic\ResponseStatus;

/**
 * AdapterChain
 *
 * @category Dhl
 * @package  Dhl\Versenden\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AdapterChain implements AdapterChainInterface
{
    /**
     * @var BcsAdapterInterfaceFactory
     */
    private $bcsAdapterFactory;

    /**
     * @var GlAdapterInterfaceFactory
     */
    private $glAdapterFactory;

    /**
     * AdapterFactory constructor.
     * @param BcsAdapterInterfaceFactory $bcsAdapterFactory
     * @param GlAdapterInterfaceFactory $glAdapterFactory
     */
    public function __construct(
        BcsAdapterInterfaceFactory $bcsAdapterFactory,
        GlAdapterInterfaceFactory $glAdapterFactory
    ) {
        $this->bcsAdapterFactory = $bcsAdapterFactory;
        $this->glAdapterFactory = $glAdapterFactory;
    }

    /**
     * @param ResponseType\CreateShipment\LabelInterface[] $labels
     * @param string[] $invalidOrders
     * @return string[]
     */
    private function getStatusMessages(array $labels, array $invalidOrders = [])
    {
        $messages = [];

        foreach ($labels as $label) {
            $messages[] = sprintf(
                '%s: %s | %s',
                $label->getSequenceNumber(),
                $label->getStatus()->getText(),
                $label->getStatus()->getMessage()
            );
        }

        foreach ($invalidOrders as $sequenceNumber => $errorMessage) {
            $messages[] = sprintf(
                '%s: %s',
                $sequenceNumber,
                $errorMessage
            );
        }

        return $messages;
    }

    /**
     * @param ResponseType\CreateShipment\LabelInterface[] $labels
     * @param string[] $invalidOrders
     * @return ResponseStatus
     */
    private function getResponseStatus(array $labels, array $invalidOrders = [])
    {
        $createdLabels = array_filter($labels, function (ResponseType\CreateShipment\LabelInterface $label) {
            return ($label->getStatus()->getCode() === ResponseStatusInterface::STATUS_SUCCESS);
        });
        $rejectedLabels = array_filter($labels, function (ResponseType\CreateShipment\LabelInterface $label) {
            return ($label->getStatus()->getCode() === ResponseStatusInterface::STATUS_FAILURE);
        });

        if (empty($rejectedLabels) && empty($invalidOrders)) {
            $responseStatus = new ResponseStatus(
                ResponseStatusInterface::STATUS_SUCCESS,
                'Info',
                self::MSG_STATUS_CREATED
            );
        } elseif (empty($createdLabels)) {
            $messages = $this->getStatusMessages($rejectedLabels, $invalidOrders);
            $responseStatus = new ResponseStatus(
                ResponseStatusInterface::STATUS_FAILURE,
                'Error',
                sprintf(self::MSG_STATUS_NOT_CREATED, implode("\n", $messages))
            );
        } else {
            $messages = $this->getStatusMessages($rejectedLabels, $invalidOrders);
            $responseStatus = new ResponseStatus(
                ResponseStatusInterface::STATUS_PARTIAL_SUCCESS,
                'Warning',
                sprintf(self::MSG_STATUS_PARTIALLY_CREATED, implode("\n", $messages))
            );
        }

        return $responseStatus;
    }

    /**
     * @param RequestType\CreateShipment\ShipmentOrderInterface[] $shipmentOrders
     * @param string[] $invalidOrders
     * @return ResponseType\CreateShipmentResponseInterface|CreateShipmentResponseCollection
     */
    public function createLabels(array $shipmentOrders, array $invalidOrders = [])
    {
        /** @var GlAdapter $glAdapter */
        $glAdapter = $this->glAdapterFactory->create();
        /** @var BcsAdapter $bcsAdapter */
        $bcsAdapter = $this->bcsAdapterFactory->create();
        $bcsAdapter->setSuccessor($glAdapter);

        try {
            $labels = $bcsAdapter->createLabels($shipmentOrders);
            $responseStatus = $this->getResponseStatus($labels, $invalidOrders);
        } catch (\Exception $e) {
            $labels = [];
            $responseStatus = new ResponseStatus(
                ResponseStatusInterface::STATUS_FAILURE,
                'Error',
                sprintf(self::MSG_STATUS_NOT_CREATED, $e->getMessage())
            );
        }

        $response = new CreateShipmentResponseCollection($responseStatus, $labels);
        return $response;
    }

    /**
     * @param string[] $shipmentNumbers
     * @return ResponseType\DeleteShipmentResponseInterface|DeleteShipmentResponseCollection
     */
    public function cancelLabels(array $shipmentNumbers)
    {
        $bcsAdapter = $this->bcsAdapterFactory->create();

        try {
            $cancelledItems = $bcsAdapter->cancelLabels($shipmentNumbers);
        } catch (\Exception $e) {
            $cancelledItems = [];
            $responseStatus = new ResponseStatus(
                ResponseStatusInterface::STATUS_FAILURE,
                'Error',
                sprintf(self::MSG_STATUS_NOT_DELETED, $e->getMessage())
            );
        }

        $response = new DeleteShipmentResponseCollection($responseStatus, $cancelledItems);
        return $response;
    }
}
