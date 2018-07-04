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
 * @package   Dhl\Shipping\ViewModel
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://www.netresearch.de/
 */
namespace Dhl\Shipping\ViewModel\Packaging;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ShippingItems
 *
 * @package Dhl\Shipping\ViewModel
 * @author  Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    https://www.netresearch.de/
 */
class ShippingItems implements ArgumentInterface
{
    /**
     * @var CountryCollectionFactory
     */
    private $countryCollectionFactory;

    /**
     * ShippingItems constructor.
     * @param CountryCollectionFactory $countryCollectionFactory
     */
    public function __construct(CountryCollectionFactory $countryCollectionFactory)
    {
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * Get countries for select field.
     *
     * @return string[]
     */
    public function getCountries()
    {
        $countryCollection = $this->countryCollectionFactory->create();
        return $countryCollection->toOptionArray();
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return string
     */
    public function getExportDescription($orderItem)
    {
        $exportDescription = $orderItem->getProduct()->getData('dhl_export_description')
            ?: $orderItem->getProduct()->getData('name');

        return substr($exportDescription, 0, 50);
    }
}
