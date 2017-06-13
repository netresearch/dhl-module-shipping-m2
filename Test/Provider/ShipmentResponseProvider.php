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
 * @category  Dhl
 * @package   Dhl\Shipping\Test\Integration
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Test\Provider;

use \Dhl\Shipping\Webservice\ResponseType\CreateShipment;
use Dhl\Shipping\Webservice\ResponseType\CreateShipmentResponseCollection;
use \Dhl\Shipping\Webservice\ResponseType\Generic\ItemStatus;
use Dhl\Shipping\Webservice\ResponseType\Generic\ResponseStatus;

/**
 * ConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Provider
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShipmentResponseProvider
{
    /**
     * @param string $sequenceNumber
     * @return CreateShipment\Label
     */
    private static function getSuccessItem($sequenceNumber)
    {
        $itemStatus = new ItemStatus(
            $sequenceNumber,
            ResponseStatus::STATUS_SUCCESS,
            'ok',
            ['Der Webservice wurde ohne Fehler ausgeführt.']
        );

        $responseLabel = new CreateShipment\Label(
            $itemStatus,
            $sequenceNumber,
            '22222221337',
            '%PDF-1.4',
            null,
            null,
            null
        );

        return $responseLabel;
    }

    /**
     * @param string $sequenceNumber
     * @return CreateShipmentResponseCollection
     */
    public static function provideSingleSuccessResponse($sequenceNumber)
    {
        $responseStatus = new ResponseStatus(
            ResponseStatus::STATUS_SUCCESS,
            'Info',
            'Shipping labels were created successfully.'
        );

        $labels = [];
        $labels[$sequenceNumber] = self::getSuccessItem($sequenceNumber);

        $response = new CreateShipmentResponseCollection($labels);
        $response->setStatus($responseStatus);
        return $response;
    }

    /**
     * @param string[] $sequenceNumbers
     * @return CreateShipmentResponseCollection
     */
    public static function provideMultipleSuccessResponse(array $sequenceNumbers)
    {
        $responseStatus = new ResponseStatus(
            ResponseStatus::STATUS_SUCCESS,
            'Info',
            'Shipping labels were created successfully.'
        );

        $labels = [];
        foreach ($sequenceNumbers as $sequenceNumber) {
            $labels[$sequenceNumber] = self::getSuccessItem($sequenceNumber);
        }

        $response = new CreateShipmentResponseCollection($responseStatus, $labels);
        return $response;
    }

    /**
     * @return CreateShipmentResponseCollection
     */
    public static function provideSingleErrorResponse()
    {
        $responseStatus = new ResponseStatus(ResponseStatus::STATUS_FAILURE, 'Error', 'Hard validation error occured.');
        $response = new CreateShipmentResponseCollection();
        $response->setStatus($responseStatus);

        return $response;
    }

    public static function provideMultipleErrorResponse()
    {

    }

    public static function provideMixedResponse()
    {

    }
}
