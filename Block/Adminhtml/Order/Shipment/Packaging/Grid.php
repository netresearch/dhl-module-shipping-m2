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
 * @package   Dhl\Shipping
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Block\Adminhtml\Order\Shipment\Packaging;

use Dhl\Shipping\Model\Attribute\Backend\ExportDescription;
use Dhl\Shipping\Model\Attribute\Source\DGCategory;
use Dhl\Shipping\Model\Attribute\Backend\TariffNumber;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use \Magento\Backend\Block\Template\Context;
use \Magento\Sales\Model\Order\Shipment\ItemFactory;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use \Magento\Framework\Registry;

/**
 * Grid
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Grid extends \Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid
{
    const BCS_GRID_TEMPLATE = 'Dhl_Shipping::order/packaging/grid_bcs.phtml';

    const GL_GRID_TEMPLATE = 'Dhl_Shipping::order/packaging/grid_gl.phtml';

    const STANDARD_TEMPLATE = 'Magento_Shipping::order/packaging/grid.phtml';

    /** @var  ModuleConfigInterface */
    private $moduleConfig;

    /** @var  ProductFactory */
    private $productFactory;

    /** @var  CountryCollection */
    private $countryCollection;

    /**
     * @var string[]
     */
    private $countriesOfManufacture = [];

    /**
     * @var string[]
     */
    private $dangerousGoodsCategories = [];

    /**
     * @var string[]
     */
    private $tariffNumbers = [];

    /**
     * @var string[]
     */
    private $exportDescriptions = [];

    /**
     * Grid constructor.
     * @param Context $context
     * @param ItemFactory $shipmentItemFactory
     * @param Registry $registry
     * @param ModuleConfigInterface $moduleConfig
     * @param ProductFactory $productFactory
     * @param CountryCollection $countryCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        ItemFactory $shipmentItemFactory,
        Registry $registry,
        ModuleConfigInterface $moduleConfig,
        ProductFactory $productFactory,
        CountryCollection $countryCollection,
        array $data = []
    ) {
        $this->countryCollection = $countryCollection;
        $this->productFactory = $productFactory;
        $this->moduleConfig = $moduleConfig;
        parent::__construct(
            $context,
            $shipmentItemFactory,
            $registry,
            $data
        );
    }

    public function getTemplate()
    {
        $originCountryId = $this->moduleConfig->getShipperCountry(
            $this->getShipment()->getStoreId()
        );
        $destCountryId = $this->getShipment()->getShippingAddress()->getCountryId();
        $bcsCountries = [
            'DE',
            'AT'
        ];

        $isCrossBorder = $this->moduleConfig->isCrossBorderRoute(
            $destCountryId,
            $this->getShipment()->getStoreId()
        );
        $usedTemplate = self::STANDARD_TEMPLATE;

        if ($isCrossBorder
            && in_array(
                $originCountryId,
                $bcsCountries
            )
        ) {
            $usedTemplate = self::BCS_GRID_TEMPLATE;
        } elseif ($isCrossBorder
            && !in_array(
                $originCountryId,
                $bcsCountries
            )
        ) {
            $usedTemplate = self::GL_GRID_TEMPLATE;
        }

        return $usedTemplate;
    }

    /**
     * Obtain the given product's tariff number
     *
     * @param int $productId
     * @return string
     */
    public function getTariffNumber($productId)
    {
        if (empty($this->tariffNumbers)) {
            /** @var \Magento\Sales\Model\Order\Shipment\Item[] $items */
            $this->initItemAttributes();
        }

        return $this->tariffNumbers[$productId];
    }

    /**
     * Obtain the "Export Description" attribute value for a given product.
     *
     * @param int $productId
     * @return string
     */
    public function getExportDescription($productId)
    {
        if (empty($this->exportDescriptions)) {
            /** @var \Magento\Sales\Model\Order\Shipment\Item[] $items */
            $this->initItemAttributes();
        }

        return $this->exportDescriptions[$productId];
    }

    /**
     * Obtain the given product's country of manufacture.
     *
     * @param int $productId
     * @return string
     */
    public function getCountryOfManufacture($productId)
    {
        if (empty($this->countriesOfManufacture)) {
            /** @var \Magento\Sales\Model\Order\Shipment\Item[] $items */
            $this->initItemAttributes();
        }

        if (!isset($this->countriesOfManufacture[$productId])) {
            // fallback to shipper country
            return $this->moduleConfig->getShipperCountry(
                $this->getShipment()->getStoreId()
            );
        }

        return $this->countriesOfManufacture[$productId];
    }

    /**
     * Get countries for select field.
     *
     * @return array
     */
    public function getCountries()
    {
        return $this->countryCollection->toOptionArray();
    }

    /**
     * @param int $productId
     * @return string
     */
    public function getDangerousGoodsCategory($productId)
    {
        if (empty($this->dangerousGoodsCategories)) {
            $this->initItemAttributes();
        }

        return $this->dangerousGoodsCategories[$productId];
    }

    /**
     * Load itemAttributes from products and assign to class members.
     */
    private function initItemAttributes()
    {
        $items = $this->getCollection();

        $productIds = array_map(
            function (\Magento\Sales\Model\Order\Shipment\Item $item) {
                return $item->getProductId();
            },
            $items
        );

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productFactory->create()->getCollection();
        $productCollection->addStoreFilter($this->getShipment()->getStoreId())
            ->addFieldToFilter(
                'entity_id',
                ['in' => $productIds]
            )->addAttributeToSelect(
                'country_of_manufacture',
                true
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

        while ($product = $productCollection->fetchItem()) {
            $this->countriesOfManufacture[$product->getId()] = $product->getData('country_of_manufacture');
            $this->dangerousGoodsCategories[$product->getId()] = $product->getData(DGCategory::CODE);
            $this->tariffNumbers[$product->getId()] = $product->getData(TariffNumber::CODE);
            $this->exportDescriptions[$product->getId()] = $product->getData(ExportDescription::CODE);
        }
    }
}
