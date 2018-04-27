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
 * @package   Dhl\Shipping\Cron\AutoCreate
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Helper;

use Dhl\Shipping\Model\Attribute\Backend\ExportDescription;
use Dhl\Shipping\Model\Attribute\Source\DGCategory;
use Dhl\Shipping\Model\Attribute\Backend\TariffNumber;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class ProductData
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 */
class ProductData
{
    /**
     * @var Collection
     */
    private $productCollection;

    /**
     * ProductData constructor.
     *
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->productCollection = $collection;
    }

    /**
     * @param array $productIds
     * @param int $storeId
     * @return array
     */
    public function getProductData(array $productIds, $storeId)
    {
        $this->productCollection->addStoreFilter($storeId)
            ->addFieldToFilter(
                'entity_id',
                ['in' => $productIds]
            )->addAttributeToSelect(
                DGCategory::CODE,
                true
            )->addAttributeToSelect(
                TariffNumber::CODE,
                true
            )->addAttributeToSelect(
                ExportDescription::CODE,
                true
            );

        $data = [];

        while ($product = $this->productCollection->fetchItem()) {
            $data[$product->getId()] = [
                DGCategory::CODE        => $product->getData(DGCategory::CODE),
                TariffNumber::CODE      => $product->getData(TariffNumber::CODE),
                ExportDescription::CODE => $product->getData(ExportDescription::CODE)

            ];
        }

        return $data;
    }
}
