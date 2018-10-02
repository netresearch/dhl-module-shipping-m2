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
 * @package   Dhl\Shipping\Model
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Service\Filter;

use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Service\Bcs\PreferredDay;
use Dhl\Shipping\Service\Filter\FilterInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Check if the service is available for customers to select and enabled via config.
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class InStockFilter implements FilterInterface
{

    /**
     * @var array
     */
    private $servicesToCheck =[PreferredDay::CODE];

    /**
     * @var \Magento\Quote\Api\Data\CartItemInterface[]
     */
    private $cartItems = [];

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * InStockFilter constructor.
     * @param StockRegistryInterface $stockRegistry
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $cartItems
     */
    public function __construct(
        StockRegistryInterface $stockRegistry,
        array $cartItems
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->cartItems = $cartItems;
    }

    /**
     * @param ServiceInterface $service
     * @return bool
     */
    public function isAllowed(ServiceInterface $service)
    {
        $serviceCode = $service->getCode();
        if (!in_array($serviceCode, $this->servicesToCheck)) {
            return true;
        }

        $notInStock = false;
        foreach ($this->cartItems as $cartItem) {
            $stockItem = $this->stockRegistry->getStockItem(
                $cartItem->getProduct()->getId(),
                $cartItem->getProduct()->getStoreId()
            );

            if ($stockItem->getQty() < $cartItem->getQty()) {
                $notInStock = true;
                break;
            }
        }

        return $notInStock ? false : true;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $cartItems
     * @param StockRegistryInterface $stockRegistry
     * @return \Closure
     */
    public static function create(array $cartItems, $stockRegistry)
    {
        return function (ServiceInterface $service) use ($cartItems, $stockRegistry) {
            $filter = new static($stockRegistry, $cartItems);
            return $filter->isAllowed($service);
        };
    }
}
