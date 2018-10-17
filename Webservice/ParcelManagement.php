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
 * @package   Dhl\Shipping\Webservice
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Webservice;

use Dhl\ParcelManagement\Api\CheckoutApi;
use Dhl\ParcelManagement\ApiException;
use Dhl\ParcelManagement\Model\AvailableServicesMap;
use Dhl\Shipping\Model\Config\BcsConfig;

/**
 * Parcel  Management API Client wrapper.
 *
 * @package  Dhl\Shipping\Webservice
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ParcelManagement
{

    const API_KEY_IDENTIFIER = 'DPDHL-User-Authentication-Token';

    /**
     * @var CheckoutApi
     */
    private $checkoutApi;

    /**
     * @var BcsConfig
     */
    private $bcsConfig;

    /**
     * @var null|AvailableServicesMap
     */
    private $serviceResponse = null;


    public function __construct(
        CheckoutApi $checkoutApi,
        BcsConfig $bcsConfig
    ) {
        $this->checkoutApi = $checkoutApi;
        $this->bcsConfig = $bcsConfig;
    }

    /**
     * @param \DateTime $dropOff Day when the shipment will be dropped by the sender in the DHL parcel center
     * @param string $postalCode
     * @return array
     * @throws ApiException
     */
    public function getPreferredDayOptions($dropOff, $postalCode)
    {
        //@Todo: use cached response if any
        $checkoutServices = $this->getCheckoutServices($dropOff, $postalCode);
        $validDays = $checkoutServices->getPreferredDay()->getValidDays();

        return $validDays;
    }


    /**
     * @param \DateTime $dropOff Day when the shipment will be dropped by the sender in the DHL parcel center
     * @param string $postalCode
     * @return \Dhl\ParcelManagement\Model\Timeframe[]
     * @throws ApiException
     */
    public function getPreferredTimeOptions($dropOff, $postalCode)
    {
        //@Todo: use cached response if any
        $checkoutServices = $this->getCheckoutServices($dropOff, $postalCode);

        return $checkoutServices->getPreferredTime()->getTimeframes();
    }


    /**
     * @param \DateTime $date
     * @param string $postalCode
     * @return \Dhl\ParcelManagement\Model\AvailableServicesMap
     * @throws \Dhl\ParcelManagement\ApiException
     */
    public function getCheckoutServices($date, $postalCode)
    {
        $ekp = $this->bcsConfig->getAccountEkp();
        $userName = $this->bcsConfig->getAuthUsername();
        $passWd = $this->bcsConfig->getAuthPassword();
        $pmApiEndpoint = $this->bcsConfig->getParcelManagementEndpoint();
        $apiKey = base64_encode($this->bcsConfig->getAccountUser() . ':' . $this->bcsConfig->getAccountSignature());

        $this->checkoutApi->getConfig()
            ->setUsername($userName)
            ->setPassword($passWd)
            ->setHost($pmApiEndpoint)
            ->setApiKey(self::API_KEY_IDENTIFIER, $apiKey);


        $response = $this->checkoutApi->checkoutRecipientZipAvailableServicesGet($ekp, $postalCode, $date);

        return $response;
    }
}