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
 * @package   Dhl\Shipping
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Webservice\Client;

use \Dhl\Shipping\Config\BcsConfigInterface;

/**
 * Business Customer Shipping API SOAP client
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class BcsSoapClient extends \SoapClient implements BcsSoapClientInterface
{
    /**
     * @var array $classmap The defined classes
     */
    private static $classmap =  [
        'Version' => 'Dhl\\Shipping\\Bcs\\Version',
        'GetVersionResponse' => 'Dhl\\Shipping\\Bcs\\GetVersionResponse',
        'CreateShipmentOrderRequest' => 'Dhl\\Shipping\\Bcs\\CreateShipmentOrderRequest',
        'CreateShipmentOrderResponse' => 'Dhl\\Shipping\\Bcs\\CreateShipmentOrderResponse',
        'ShipmentOrderType' => 'Dhl\\Shipping\\Bcs\\ShipmentOrderType',
        'Shipment' => 'Dhl\\Shipping\\Bcs\\Shipment',
        'ShipmentDetailsTypeType' => 'Dhl\\Shipping\\Bcs\\ShipmentDetailsTypeType',
        'ShipmentDetailsType' => 'Dhl\\Shipping\\Bcs\\ShipmentDetailsType',
        'ShipmentItemType' => 'Dhl\\Shipping\\Bcs\\ShipmentItemType',
        'ShipmentService' => 'Dhl\\Shipping\\Bcs\\ShipmentService',
        'ServiceconfigurationDateOfDelivery' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationDateOfDelivery',
        'ServiceconfigurationDeliveryTimeframe' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationDeliveryTimeframe',
        'ServiceconfigurationISR' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationISR',
        'Serviceconfiguration' => 'Dhl\\Shipping\\Bcs\\Serviceconfiguration',
        'ServiceconfigurationShipmentHandling' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationShipmentHandling',
        'ServiceconfigurationEndorsement' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationEndorsement',
        'ServiceconfigurationVisualAgeCheck' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationVisualAgeCheck',
        'ServiceconfigurationDetails' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationDetails',
        'ServiceconfigurationCashOnDelivery' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationCashOnDelivery',
        'ServiceconfigurationAdditionalInsurance' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationAdditionalInsurance',
        'ServiceconfigurationIC' => 'Dhl\\Shipping\\Bcs\\ServiceconfigurationIC',
        'Ident' => 'Dhl\\Shipping\\Bcs\\Ident',
        'ShipmentNotificationType' => 'Dhl\\Shipping\\Bcs\\ShipmentNotificationType',
        'BankType' => 'Dhl\\Shipping\\Bcs\\BankType',
        'ShipperType' => 'Dhl\\Shipping\\Bcs\\ShipperType',
        'ShipperTypeType' => 'Dhl\\Shipping\\Bcs\\ShipperTypeType',
        'NameType' => 'Dhl\\Shipping\\Bcs\\NameType',
        'NativeAddressType' => 'Dhl\\Shipping\\Bcs\\NativeAddressType',
        'CountryType' => 'Dhl\\Shipping\\Bcs\\CountryType',
        'CommunicationType' => 'Dhl\\Shipping\\Bcs\\CommunicationType',
        'ReceiverType' => 'Dhl\\Shipping\\Bcs\\ReceiverType',
        'ReceiverTypeType' => 'Dhl\\Shipping\\Bcs\\ReceiverTypeType',
        'ReceiverNativeAddressType' => 'Dhl\\Shipping\\Bcs\\ReceiverNativeAddressType',
        'cis:PackStationType' => 'Dhl\\Shipping\\Bcs\\PackStationType',
        'cis:PostfilialeType' => 'Dhl\\Shipping\\Bcs\\PostfilialeType',
        'cis:ParcelShopType' => 'Dhl\\Shipping\\Bcs\\ParcelShopType',
        'ExportDocumentType' => 'Dhl\\Shipping\\Bcs\\ExportDocumentType',
        'ExportDocPosition' => 'Dhl\\Shipping\\Bcs\\ExportDocPosition',
        'Statusinformation' => 'Dhl\\Shipping\\Bcs\\Statusinformation',
        'CreationState' => 'Dhl\\Shipping\\Bcs\\CreationState',
        'LabelData' => 'Dhl\\Shipping\\Bcs\\LabelData',
        'DeleteShipmentOrderRequest' => 'Dhl\\Shipping\\Bcs\\DeleteShipmentOrderRequest',
        'DeleteShipmentOrderResponse' => 'Dhl\\Shipping\\Bcs\\DeleteShipmentOrderResponse',
        'DeletionState' => 'Dhl\\Shipping\\Bcs\\DeletionState',
    ];

    /**
     * BcsSoapClient constructor.
     * @param BcsConfigInterface $config
     * @param string|null $wsdl
     * @param string[]|null $options
     */
    public function __construct(BcsConfigInterface $config, $wsdl, array $options = null)
    {
        $bcsOptions = [
            'location' => $config->getApiEndpoint(),
            'login'    => $config->getAuthUsername(),
            'password' => $config->getAuthPassword(),
            'classmap' => self::$classmap,
            'trace'    => true,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
        ];

        $options = empty($options) ? $bcsOptions : array_merge($options, $bcsOptions);

        parent::__construct($wsdl, $options);

        $authHeader = new \SoapHeader(
            'http://dhl.de/webservice/cisbase',
            'Authentification',
            [
                'user'      => $config->getAccountUser(),
                'signature' => $config->getAccountSignature(),
            ]
        );
        $this->__setSoapHeaders($authHeader);
    }

    /**
     * Returns the actual version of the implementation of the whole ISService
     *         webservice.
     *
     * @param \Dhl\Shipping\Bcs\Version $request
     *
     * @return \Dhl\Shipping\Bcs\GetVersionResponse
     */
    public function getVersion(\Dhl\Shipping\Bcs\Version $request)
    {
        return $this->__soapCall('getVersion', [$request]);
    }

    /**
     * Creates shipments.
     *
     * @param \Dhl\Shipping\Bcs\CreateShipmentOrderRequest $request
     *
     * @return \Dhl\Shipping\Bcs\CreateShipmentOrderResponse
     */
    public function createShipmentOrder(\Dhl\Shipping\Bcs\CreateShipmentOrderRequest $request)
    {
        return $this->__soapCall('createShipmentOrder', [$request]);
    }

    /**
     * Deletes the requested shipments.
     *
     * @param \Dhl\Shipping\Bcs\DeleteShipmentOrderRequest $request
     *
     * @return \Dhl\Shipping\Bcs\DeleteShipmentOrderResponse
     */
    public function deleteShipmentOrder(\Dhl\Shipping\Bcs\DeleteShipmentOrderRequest $request)
    {
        return $this->__soapCall('deleteShipmentOrder', [$request]);
    }

    /**
     * @return string
     */
    public function getLastRequest()
    {
        return $this->__getLastRequest();
    }

    /**
     * @return string
     */
    public function getLastRequestHeaders()
    {
        return $this->__getLastRequestHeaders();
    }

    /**
     * @return string
     */
    public function getLastResponse()
    {
        return $this->__getLastResponse();
    }

    /**
     * @return string
     */
    public function getLastResponseHeaders()
    {
        return $this->__getLastResponseHeaders();
    }
}
