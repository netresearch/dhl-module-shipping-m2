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
 * @package   Dhl\Versenden
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Versenden\Model\Plugin;

use \Dhl\Versenden\Api\Data\Webservice\Request\Type\CreateShipment\ShipmentOrderInterface;
use \Dhl\Versenden\Api\Data\Webservice\Response\Type\CreateShipment\LabelInterface;
use \Dhl\Versenden\Api\Webservice\Client\BcsSoapClientInterface;
use \Dhl\Versenden\Webservice\Adapter\BcsAdapter;
use \Dhl\Versenden\Webservice\Logger;

/**
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class BcsAdapterPlugin
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var BcsSoapClientInterface
     */
    private $soapClient;

    /**
     * @param BcsSoapClientInterface $soapClient
     * @param Logger                 $logger
     *
     * @codeCoverageIgnore
     */
    public function __construct(BcsSoapClientInterface $soapClient, Logger $logger)
    {
        $this->soapClient = $soapClient;
        $this->logger     = $logger;
    }

    /**
     * Will be called, the moment, the Bcs adapter calls the HTTP Client
     *
     * @param BcsAdapter               $subject
     * @param callable                 $proceed
     * @param ShipmentOrderInterface[] $shipmentOrders
     *
     * @return LabelInterface[]
     * @throws \SoapFault
     */
    public function aroundCreateLabels(BcsAdapter $subject, callable $proceed, array $shipmentOrders)
    {
        try {
            $labels = $proceed($shipmentOrders);
            /** @var LabelInterface $label */
            foreach ($labels as $label) {
                $labelStatus = $label->getStatus();
                if ($labelStatus->getCode() == '0') {
                    $this->logger->log(\Monolog\Logger::DEBUG, $this->soapClient->getLastRequest());
                    $this->logger->log(\Monolog\Logger::DEBUG, $this->soapClient->getLastResponseHeaders());
                    $this->logger->log(\Monolog\Logger::DEBUG, $this->soapClient->getLastResponse());
                } else {
                    $this->logger->log(\Monolog\Logger::WARNING, $this->soapClient->getLastRequest());
                    $this->logger->log(\Monolog\Logger::WARNING, $this->soapClient->getLastResponseHeaders());
                    $this->logger->log(\Monolog\Logger::WARNING, $this->soapClient->getLastResponse());
                }
            }

            return $labels;
        } catch (\SoapFault $e) {
            $this->logger->log(\Monolog\Logger::ERROR, $this->soapClient->getLastRequest());
            $this->logger->log(\Monolog\Logger::ERROR, $this->soapClient->getLastResponseHeaders());
            $this->logger->log(\Monolog\Logger::ERROR, $this->soapClient->getLastResponse());
            throw $e;
        }
    }
}
