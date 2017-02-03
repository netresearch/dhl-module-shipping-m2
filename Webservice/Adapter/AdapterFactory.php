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

use \Dhl\Versenden\Api\Webservice\AdapterInterface;
use \Dhl\Versenden\Api\Webservice\Adapter\GkAdapterFactory;
use \Dhl\Versenden\Api\Webservice\Adapter\GlAdapterFactory;

/**
 * AdapterFactory
 *
 * @category Dhl
 * @package  Dhl\Versenden\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AdapterFactory
{
    /**
     * @var GkAdapterFactory
     */
    private $gkAdapterFactory;

    /**
     * @var GlAdapterFactory
     */
    private $glAdapterFactory;

    /**
     * List of shared instances
     *
     * @var AdapterInterface[]
     */
    private $sharedInstances = [];

    /**
     * AdapterFactory constructor.
     * @param GkAdapterFactory $gkAdapterFactory
     * @param GlAdapterFactory $glAdapterFactory
     */
    public function __construct(GkAdapterFactory $gkAdapterFactory, GlAdapterFactory $glAdapterFactory)
    {
        $this->gkAdapterFactory = $gkAdapterFactory;
        $this->glAdapterFactory = $glAdapterFactory;
    }

    /**
     * @param string $shipperAddressCountryCode
     * @return string
     */
    public static function getAdapterType($shipperAddressCountryCode)
    {
        switch ($shipperAddressCountryCode) {
            case 'DE':
            case 'AT':
                return AdapterInterface::ADAPTER_TYPE_GK;
            default:
                return AdapterInterface::ADAPTER_TYPE_GL;
        }
    }

    /**
     * @param string $shipperAddressCountryCode
     * @return AdapterInterface
     * @throws \Exception
     */
    public function create($shipperAddressCountryCode)
    {
        $type = self::getAdapterType($shipperAddressCountryCode);
        switch ($type) {
            case AdapterInterface::ADAPTER_TYPE_GK:
                return $this->gkAdapterFactory->create();
            case AdapterInterface::ADAPTER_TYPE_GL:
                return $this->glAdapterFactory->create();
        }

        //TODO(nr): introduce separate exception type
        throw new \Exception("Could not create adapter for shipping origin $shipperAddressCountryCode");
    }

    /**
     * @param string $shipperAddressCountryCode
     * @return AdapterInterface
     */
    public function get($shipperAddressCountryCode)
    {
        $type = self::getAdapterType($shipperAddressCountryCode);
        if (!isset($this->sharedInstances[$type])) {
            $this->sharedInstances[$type] = $this->create($shipperAddressCountryCode);
        }

        return $this->sharedInstances[$type];
    }
}
