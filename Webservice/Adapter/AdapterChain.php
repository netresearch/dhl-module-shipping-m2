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

use \Dhl\Versenden\Api\Data\Webservice\Request;
use \Dhl\Versenden\Api\Data\Webservice\Response;
use \Dhl\Versenden\Api\Data\Webservice\Response\Type\Generic\ResponseStatusInterface;
use \Dhl\Versenden\Api\Webservice\Adapter\AdapterChainInterface;
use \Dhl\Versenden\Api\Webservice\Adapter\BcsAdapterInterfaceFactory;
use \Dhl\Versenden\Api\Webservice\Adapter\GlAdapterInterfaceFactory;
use \Dhl\Versenden\Webservice\Response\Type\CreateShipmentResponseCollection;
use \Dhl\Versenden\Webservice\Response\Type\DeleteShipmentResponseCollection;
use \Dhl\Versenden\Webservice\Response\Type\Generic\ResponseStatus;

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
     * @param Response\Type\CreateShipment\LabelInterface[] $labels
     * @return string[]
     */
    private function getResponseMessages(array $labels)
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

        return $messages;
    }

    /**
     * @param Response\Type\CreateShipment\LabelInterface[] $labels
     * @return ResponseStatus
     */
    private function getResponseStatus(array $labels)
    {
        $createdLabels = array_filter($labels, function (Response\Type\CreateShipment\LabelInterface $label) {
            return ($label->getStatus()->getCode() === ResponseStatusInterface::STATUS_SUCCESS);
        });
        $rejectedLabels = array_filter($labels, function (Response\Type\CreateShipment\LabelInterface $label) {
            return ($label->getStatus()->getCode() === ResponseStatusInterface::STATUS_FAILURE);
        });

        if (empty($rejectedLabels)) {
            $responseStatus = new ResponseStatus(
                ResponseStatusInterface::STATUS_SUCCESS,
                'Info',
                self::MSG_STATUS_CREATED
            );
        } elseif (empty($createdLabels)) {
            $messages = $this->getResponseMessages($rejectedLabels);
            $responseStatus = new ResponseStatus(
                ResponseStatusInterface::STATUS_FAILURE,
                'Error',
                sprintf(self::MSG_STATUS_NOT_CREATED, implode("\n", $messages))
            );
        } else {
            $messages = $this->getResponseMessages($rejectedLabels);
            $responseStatus = new ResponseStatus(
                ResponseStatusInterface::STATUS_PARTIAL_SUCCESS,
                'Warning',
                sprintf(self::MSG_STATUS_PARTIALLY_CREATED, implode("\n", $messages))
            );
        }

        return $responseStatus;
    }

    /**
     * @param Request\Type\CreateShipment\ShipmentOrderInterface[] $shipmentOrders
     * @return Response\Type\CreateShipmentResponseInterface|CreateShipmentResponseCollection
     */
    public function createLabels(array $shipmentOrders)
    {
        /** @var GlAdapter $glAdapter */
        $glAdapter = $this->glAdapterFactory->create();
        /** @var BcsAdapter $bcsAdapter */
        $bcsAdapter = $this->bcsAdapterFactory->create();
        $bcsAdapter->setSuccessor($glAdapter);

        try {
            $labels = $bcsAdapter->createLabels($shipmentOrders);
            $responseStatus = $this->getResponseStatus($labels);
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
     * @return Response\Type\DeleteShipmentResponseInterface|DeleteShipmentResponseCollection
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
