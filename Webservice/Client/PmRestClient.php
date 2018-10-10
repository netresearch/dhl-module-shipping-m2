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
namespace Dhl\Shipping\Webservice\Client;

use Dhl\ParcelManagement\Api\CheckoutApi;
use Dhl\Shipping\Model\Config\BcsConfig;

/**
 * Parcel Management API REST client wrapper.
 *
 * @package  Dhl\Shipping\Webservice
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */

class PmRestClient
{
    /**
     * @var CheckoutApi
     */
    private $checkoutApi;

    /**
     * @var BcsConfig
     */
    private $bcsConfig;

    public function __construct(
        CheckoutApi $checkoutApi,
        BcsConfig $bcsConfig
    ) {
        $this->checkoutApi = $checkoutApi;
        $this->bcsConfig = $bcsConfig;
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


