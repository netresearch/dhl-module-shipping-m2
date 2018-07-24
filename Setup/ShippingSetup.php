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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Setup;

use Dhl\Shipping\Config\BcsConfigInterface;
use Dhl\Shipping\Model\Attribute\Backend\ExportDescription;
use Dhl\Shipping\Model\Attribute\Source\DGCategory;
use Dhl\Shipping\Model\Attribute\Backend\TariffNumber;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * ShippingSetup
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingSetup
{
    const TABLE_QUOTE_ADDRESS = 'dhlshipping_quote_address';
    const TABLE_ORDER_ADDRESS = 'dhlshipping_order_address';
    const TABLE_LABEL_STATUS = 'dhlshipping_label_status';

    /**
     * @param EavSetup $eavSetup
     */
    public static function addDangerousGoodsCategoryAttribute(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            DGCategory::CODE,
            [
                'group' => '',
                'type' => 'varchar',
                'label' => 'Dangerous Goods Category',
                'input' => 'select',
                'required' => false,
                'source' => DGCategory::class,
                'sort_order' => 50,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
            ]
        );
    }

    /**
     * @param EavSetup $eavSetup
     */
    public static function addTariffNumberAttribute(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            TariffNumber::CODE,
            [
                'group' => '',
                'type' => 'varchar',
                'label' => 'Tariff Number',
                'input' => 'text',
                'required' => false,
                'sort_order' => 50,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'backend' => TariffNumber::class,
                'visible' => true,
            ]
        );
    }

    /**
     * @param EavSetup $eavSetup
     */
    public static function addExportDescriptionAttribute(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            ExportDescription::CODE,
            [
                'group' => '',
                'type' => 'varchar',
                'label' => 'DHL Item Description',
                'input' => 'text',
                'required' => false,
                'sort_order' => 50,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'backend' => ExportDescription::class,
                'visible' => true,
            ]
        );
    }

    /**
     * Transcode `core_config_data` entries from legacy serialized into json format.
     * Conversion is also supposed to happen in M2.1 environments where the core
     * converter and serializer classes do not yet exist.
     *
     * @link http://devdocs.magento.com/guides/v2.2/ext-best-practices/tutorials/serialized-to-json-data-upgrade.html
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     */
    public static function convertSerializedToJson($scopeConfig, $configWriter)
    {
        // read value from the legacy config path
        $legacyValue = $scopeConfig->getValue(BcsConfigInterface::CONFIG_XML_PATH_ACCOUNT_PARTICIPATION);

        // if participation numbers are available, save them json encoded to new config path
        if (!empty($legacyValue)) {
            $configWriter->save(
                BcsConfigInterface::CONFIG_XML_PATH_ACCOUNT_PARTICIPATIONS,
                json_encode(unserialize($legacyValue))
            );
            $configWriter->delete(BcsConfigInterface::CONFIG_XML_PATH_ACCOUNT_PARTICIPATION);
        }
    }

    /**
     * Create table dhlshipping_label_status
     *
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public static function createLabelStatusTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
                       ->newTable($setup->getTable(self::TABLE_LABEL_STATUS));
        $table->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['identity'  => false, 'unsigned'  => true, 'nullable'  => false],
            'Entitiy Id'
        )->addColumn(
            'status_code',
            Table::TYPE_TEXT,
            10,
            ['default' => 0, 'unsigned' => true, 'nullable' => false],
            'Status Code'
        )->addIndex(
            $setup->getIdxName(
                $setup->getTable(self::TABLE_LABEL_STATUS),
                'order_id',
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            'order_id',
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable(self::TABLE_LABEL_STATUS),
                'order_id',
                $setup->getTable('sales_order'),
                'entity_id'
            ),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_grid'),
            'dhlshipping_label_status',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 10,
                'comment' => 'DHL Shipping Label Status'
            ]
        );
    }
}
