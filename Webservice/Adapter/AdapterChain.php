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
 * @package   Dhl\Shipping\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Webservice\Adapter;

use \Dhl\Shipping\Webservice\RequestType;
use \Dhl\Shipping\Webservice\ResponseType;
use Dhl\Shipping\Webservice\Exception\ApiAdapterException;

/**
 * AdapterChain
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AdapterChain
{
    /**
     * @var AbstractAdapter[]
     */
    private $adapters = [];

    /**
     * AdapterChain constructor.
     * @param AbstractAdapter[] $adapters
     */
    public function __construct(array $adapters)
    {
        $this->adapters = $adapters;
    }

    /**
     * Prepare the adapter chain
     *
     * @return AdapterInterface
     */
    private function getAdapter()
    {
        /** @var AbstractAdapter[] $adapters */
        $adapters = array_values($this->adapters);
        if (empty($adapters)) {
            return null;
        }

        $adapterCount = count($adapters);
        for ($i = 1; $i < $adapterCount; $i++) {
            $adapter = $adapters[$i-1];
            $successor = $adapters[$i];

            $adapter->setSuccessor($successor);
        }

        return $adapters[0];
    }

    /**
     * @param RequestType\CreateShipment\ShipmentOrderInterface[] $shipmentOrders
     * @return ResponseType\CreateShipment\LabelInterface[]
     * @throws ApiAdapterException
     */
    public function createLabels(array $shipmentOrders)
    {
        $labels = $this->getAdapter()->createLabels($shipmentOrders);
        return $labels;
    }

    /**
     * @param string[] $shipmentNumbers
     * @return ResponseType\Generic\ItemStatusInterface[]
     * @throws ApiAdapterException
     */
    public function cancelLabels(array $shipmentNumbers)
    {
        $cancelledItems = $this->getAdapter()->cancelLabels($shipmentNumbers);
        return $cancelledItems;
    }
}
