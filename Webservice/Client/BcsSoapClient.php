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
 * @package   Dhl\Versenden\Api
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Webservice\Client;

use \Dhl\Versenden\Api\Webservice\ConfigInterface as ApiConfigInterface;
use \Dhl\Versenden\Api\Config\BcsConfigInterface as ModuleConfigInterface;
use \Dhl\Versenden\Api\Webservice\Client\BcsSoapClientInterface;

/**
 * Business Customer Shipping API SOAP client adapter
 *
 * @category Dhl
 * @package  Dhl\Versenden\Api
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class BcsSoapClient implements BcsSoapClientInterface
{
    /**
     * @var \Dhl\Versenden\Bcs\GVAPI_2_0_de
     */
    private $soapClient;

    /**
     * BcsSoapClient constructor.
     * @param ApiConfigInterface $apiConfig
     * @param ModuleConfigInterface $bcsConfig
     */
    public function __construct(ApiConfigInterface $apiConfig, ModuleConfigInterface $bcsConfig)
    {
        //TODO(nr): maybe or maybe not use m2 factory
        $options = [
            'location' => $apiConfig->getApiEndpoint(),
            'login' => $apiConfig->getAuthUsername(),
            'password' => $apiConfig->getAuthPassword(),
            'trace' => 1
        ];
        $client = new \Dhl\Versenden\Bcs\GVAPI_2_0_de($options);

        $authHeader = new \SoapHeader(
            'http://dhl.de/webservice/cisbase',
            'Authentification',
            [
                'user' => $bcsConfig->getAccountUser(),
                'signature' => $bcsConfig->getAccountSignature(),
            ]
        );
        $client->__setSoapHeaders($authHeader);

        $this->soapClient = $client;
    }

    /**
     * Returns the actual version of the implementation of the whole ISService
     *         webservice.
     *
     * @param \Dhl\Versenden\Bcs\Version $request
     * @return \Dhl\Versenden\Bcs\GetVersionResponse
     */
    public function getVersion(\Dhl\Versenden\Bcs\Version $request)
    {
        return $this->soapClient->getVersion($request);
    }

    /**
     * Creates shipments.
     *
     * @param \Dhl\Versenden\Bcs\CreateShipmentOrderRequest $request
     * @return \Dhl\Versenden\Bcs\CreateShipmentOrderResponse
     */
    public function createShipmentOrder(\Dhl\Versenden\Bcs\CreateShipmentOrderRequest $request)
    {
        return $this->soapClient->createShipmentOrder($request);
    }

    /**
     * Deletes the requested shipments.
     *
     * @param \Dhl\Versenden\Bcs\DeleteShipmentOrderRequest $request
     * @return \Dhl\Versenden\Bcs\DeleteShipmentOrderResponse
     */
    public function deleteShipmentOrder(\Dhl\Versenden\Bcs\DeleteShipmentOrderRequest $request)
    {
        return $this->soapClient->deleteShipmentOrder($request);
    }
}
