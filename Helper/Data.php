<?php
/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 13.04.18
 * Time: 08:49
 */

namespace Dhl\Shipping\Helper;

use Dhl\Shipping\Model\Attribute\Backend\ExportDescription;
use Dhl\Shipping\Model\Attribute\Source\DGCategory;
use Dhl\Shipping\Model\Attribute\Backend\TariffNumber;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Data
{
    private $productCollection;



    public function __construct(Collection $collection)
    {
        $this->productCollection = $collection;
    }

    public function getProductData($productIds, $storeId)
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
                TariffNumber::CODE      =>$product->getData(TariffNumber::CODE),
                ExportDescription::CODE =>$product->getData(ExportDescription::CODE)

            ];
        }

        return $data;
    }
}
