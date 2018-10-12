<?php

namespace Dhl\Shipping\Webservice;

use Dhl\ParcelManagement\Api\CheckoutApi;
use Dhl\ParcelManagement\ApiException;
use Dhl\ParcelManagement\Model\AvailableServicesMap;
use Dhl\Shipping\Model\Config\BcsConfig;

class ParcelManagement
{
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
     * @param string $consumerZip Postal code
     * @return array
     * @throws ApiException
     */
    public function getPreferredDayOptions($dropOff, $consumerZip)
    {
        //@Todo: use cached response if any
        $checkoutServices = $this->getCheckoutServices($dropOff, $consumerZip);

        $options = $checkoutServices->getPreferredDay()->getValidDays();

        return $options;
    }


    /**
     * @param \DateTime $dropOff Day when the shipment will be dropped by the sender in the DHL parcel center
     * @param string $consumerZip Postal code
     * @return array
     * @throws ApiException
     */
    public function getPreferredTimeOptions($dropOff, $consumerZip)
    {
        //@Todo: use cached response if any
        $checkoutServices = $this->getCheckoutServices($dropOff,$consumerZip);

        return $checkoutServices->getPreferredTime()->getTimeframes();
    }


    /**
     * @param \DateTime $date
     * @param string $zip
     * @return \Dhl\ParcelManagement\Model\AvailableServicesMap
     * @throws \Dhl\ParcelManagement\ApiException
     */
    public function getCheckoutServices($date, $zip)
    {
        $ekp = $this->bcsConfig->getAccountEkp();
        $userName = $this->bcsConfig->getAuthUsername();
        $passWd   = $this->bcsConfig->getAuthPassword();
        $pmApiEndpoint = $this->bcsConfig->getParcelManagementEndpoint();

        $this->checkoutApi->getConfig()
            ->setUsername($userName)
            ->setPassword($passWd)
            ->setHost($pmApiEndpoint);

        $response = $this->checkoutApi->checkoutRecipientZipAvailableServicesGet($ekp, $zip, $date);

        return $response;
    }
}