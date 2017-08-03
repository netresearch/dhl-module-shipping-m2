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

    const VALID_PDF_STRING = <<<PDF
%PDF-1.1
%¥±ë

1 0 obj
  << /Type /Catalog
     /Pages 2 0 R
  >>
endobj

2 0 obj
  << /Type /Pages
     /Kids [3 0 R]
     /Count 1
     /MediaBox [0 0 300 144]
  >>
endobj

3 0 obj
  <<  /Type /Page
      /Parent 2 0 R
      /Resources
       << /Font
           << /F1
               << /Type /Font
                  /Subtype /Type1
                  /BaseFont /Times-Roman
               >>
           >>
       >>
      /Contents 4 0 R
  >>
endobj

4 0 obj
  << /Length 55 >>
stream
  BT
    /F1 18 Tf
    0 0 Td
    (Hello World) Tj
  ET
endstream
endobj

xref
0 5
0000000000 65535 f 
0000000018 00000 n 
0000000077 00000 n 
0000000178 00000 n 
0000000457 00000 n 
trailer
  <<  /Root 1 0 R
      /Size 5
  >>
startxref
565
%%EOF
PDF;

    /**
     * @param string $sequenceNumber
     * @param bool $withTracking
     * @return CreateShipment\Label
     */
    private static function getSuccessItem($sequenceNumber, $withTracking = true)
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
            $withTracking ? '22222221337' : null,
            self::VALID_PDF_STRING,
            null,
            null,
            null
        );

        return $responseLabel;
    }

    /**
     * @param string $sequenceNumber
     * @return CreateShipment\Label
     */
    private static function getSuccessItemWithoutTrackingId($sequenceNumber)
    {
        return self::getSuccessItem($sequenceNumber, false);
    }

    /**
     * @param string $sequenceNumber
     * @param bool $withTracking
     * @return CreateShipmentResponseCollection
     */
    public static function provideSingleSuccessResponse($sequenceNumber, $withTracking = true)
    {
        $responseStatus = new ResponseStatus(
            ResponseStatus::STATUS_SUCCESS,
            'Info',
            'Shipping labels were created successfully.'
        );

        $labels = [];
        if($withTracking){
            $labels[$sequenceNumber] = self::getSuccessItem($sequenceNumber);
        } else {
            $labels[$sequenceNumber] = self::getSuccessItemWithoutTrackingId($sequenceNumber);
        }

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
