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
namespace Dhl\Versenden\Webservice\Response\Parser;

use \Dhl\Versenden\Api\Webservice\AdapterInterface;
use \Dhl\Versenden\Api\Webservice\Response\Parser\CreateShipmentParserInterface;
use \Dhl\Versenden\Api\Webservice\Response\Parser\GkCreateShipmentParserFactory;
use \Dhl\Versenden\Api\Webservice\Response\Parser\GlCreateShipmentParserFactory;
use \Dhl\Versenden\Webservice\Adapter\AdapterFactory;

/**
 * ParserFactory
 *
 * @category Dhl
 * @package  Dhl\Versenden\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CreateShipmentParserFactory
{
    /**
     * @var GkCreateShipmentParserFactory
     */
    private $gkParserFactory;

    /**
     * @var GlCreateShipmentParserFactory
     */
    private $glParserFactory;

    /**
     * List of shared instances
     *
     * @var CreateShipmentParserInterface[]
     */
    private $sharedInstances = [];

    /**
     * AdapterFactory constructor.
     * @param GkCreateShipmentParserFactory $gkParserFactory
     * @param GlCreateShipmentParserFactory $glParserFactory
     */
    public function __construct(
        GkCreateShipmentParserFactory $gkParserFactory,
        GlCreateShipmentParserFactory $glParserFactory
    ) {
        $this->gkParserFactory = $gkParserFactory;
        $this->glParserFactory = $glParserFactory;
    }

    /**
     * @param string $shipperAddressCountryCode
     * @return CreateShipmentParserInterface
     * @throws \Exception
     */
    public function create($shipperAddressCountryCode)
    {
        $type = AdapterFactory::getAdapterType($shipperAddressCountryCode);
        switch ($type) {
            case AdapterInterface::ADAPTER_TYPE_GK:
                return $this->gkParserFactory->create();
            case AdapterInterface::ADAPTER_TYPE_GL:
                return $this->glParserFactory->create();
        }

        //TODO(nr): introduce separate exception type
        throw new \Exception("Could not create response parser for shipping origin $shipperAddressCountryCode");
    }

    /**
     * @param string $shipperAddressCountryCode
     * @return CreateShipmentParserInterface
     */
    public function get($shipperAddressCountryCode)
    {
        $type = AdapterFactory::getAdapterType($shipperAddressCountryCode);
        if (!isset($this->sharedInstances[$type])) {
            $this->sharedInstances[$type] = $this->create($shipperAddressCountryCode);
        }

        return $this->sharedInstances[$type];
    }
}
