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

use \Dhl\Versenden\Api\Webservice\Adapter\AdapterInterface;
use \Dhl\Versenden\Webservice\Adapter\BcsAdapterFactory;
use \Dhl\Versenden\Webservice\Adapter\GlAdapterFactory;

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
    const ADAPTER_TYPE_GK = 'Business Customer Shipping';
    const ADAPTER_TYPE_GL = 'Global Label API';

    /**
     * @var BcsAdapterFactory
     */
    private $bcsAdapterFactory;

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
     * @param BcsAdapterFactory $bcsAdapterFactory
     * @param GlAdapterFactory $glAdapterFactory
     */
    public function __construct(BcsAdapterFactory $bcsAdapterFactory, GlAdapterFactory $glAdapterFactory)
    {
        $this->bcsAdapterFactory = $bcsAdapterFactory;
        $this->glAdapterFactory = $glAdapterFactory;
    }

    /**
     * @param string $shipperAddressCountryCode
     * @return string
     */
    public function getAdapterType($shipperAddressCountryCode)
    {
        switch ($shipperAddressCountryCode) {
            case 'DE':
            case 'AT':
                return self::ADAPTER_TYPE_GK;
            default:
                return self::ADAPTER_TYPE_GL;
        }
    }

    /**
     * @param string $adapterType
     * @return AdapterInterface
     * @throws \Exception
     */
    public function create($adapterType)
    {
        switch ($adapterType) {
            case self::ADAPTER_TYPE_GK:
                return $this->bcsAdapterFactory->create();
            case self::ADAPTER_TYPE_GL:
                return $this->glAdapterFactory->create();
        }

        //TODO(nr): introduce separate exception type
        throw new \Exception("Could not create adapter for $adapterType");
    }

    /**
     * @param string $shipperAddressCountryCode
     * @return AdapterInterface
     */
    public function get($adapterType)
    {
        if (!isset($this->sharedInstances[$adapterType])) {
            $this->sharedInstances[$adapterType] = $this->create($adapterType);
        }

        return $this->sharedInstances[$adapterType];
    }
}
