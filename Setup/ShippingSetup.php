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
 * @package   Dhl\Shipping\Setup
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Setup;

use Dhl\Shipping\Config\BcsConfigInterface;
use Dhl\Shipping\Model\Attribute\Backend\ExportDescription;
use Dhl\Shipping\Model\Attribute\Backend\TariffNumber;
use Dhl\Shipping\Model\Attribute\Source\DGCategory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * ShippingSetup
 *
 * @package  Dhl\Shipping\Setup
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingSetup
{
    const TABLE_QUOTE_ADDRESS = 'dhlshipping_quote_address';
    const TABLE_ORDER_ADDRESS = 'dhlshipping_order_address';
    const TABLE_QUOTE_SERVICE_SELECTION = 'dhlshipping_quote_address_service_selection';
    const TABLE_ORDER_SERVICE_SELECTION = 'dhlshipping_order_address_service_selection';

    const QUOTE_TABLE_NAME = 'quote';
    const QUOTE_ADDRESS_TABLE_NAME = 'quote_address';
    const ORDER_TABLE_NAME = 'sales_order';
    const INVOICE_TABLE_NAME = 'sales_invoice';
    const CREDITMEMO_TABLE_NAME = 'sales_creditmemo';
    const SERVICE_CHARGE_FIELD_NAME = 'dhl_service_charge';
    const SERVICE_CHARGE_BASE_FIELD_NAME = 'base_dhl_service_charge';

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
     * @param SerializerInterface $serializer
     */
    public static function convertSerializedToJson($scopeConfig, $configWriter, $serializer)
    {
        // read value from the legacy config path
        $legacyValue = $scopeConfig->getValue(BcsConfigInterface::CONFIG_XML_PATH_ACCOUNT_PARTICIPATION);

        // if participation numbers are available, save them json encoded to new config path
        if (!empty($legacyValue)) {
            $configWriter->save(
                BcsConfigInterface::CONFIG_XML_PATH_ACCOUNT_PARTICIPATIONS,
                json_encode($serializer->unserialize($legacyValue))
            );
            $configWriter->delete(BcsConfigInterface::CONFIG_XML_PATH_ACCOUNT_PARTICIPATION);
        }
    }

    /**
     * Create tables
     *
     * * dhlshipping_quote_address_service_selection and
     * * dhlshipping_order_address_service_selection
     *
     * to store service selections during checkout.
     *
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    public static function createServiceSelectionTables(SchemaSetupInterface $setup)
    {
        $columns = [
            [
                'name' => 'entity_id',
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            ],
            [
                'name' => 'parent_id',
                'type' => Table::TYPE_INTEGER,
                'size' => null,
                'options' => ['unsigned' => true, 'nullable' => false]
            ],
            [
                'name' => 'service_code',
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => ['identity' => false, 'unsigned' => true, 'nullable' => false],
            ],
            [
                'name' => 'service_value',
                'type' => Table::TYPE_TEXT,
                'size' => null,
                'options' => ['identity' => false, 'unsigned' => true, 'nullable' => false],
            ],
        ];

        $quoteTable = $setup->getConnection()->newTable(
            $setup->getTable(self::TABLE_QUOTE_SERVICE_SELECTION)
        );
        $orderTable = $setup->getConnection()->newTable(
            $setup->getTable(self::TABLE_ORDER_SERVICE_SELECTION)
        );
        foreach ($columns as $column) {
            $quoteTable->addColumn($column['name'], $column['type'], $column['size'], $column['options']);
            $orderTable->addColumn($column['name'], $column['type'], $column['size'], $column['options']);
        }
        $quoteTable->addForeignKey(
            $setup->getFkName(
                $setup->getTable(self::TABLE_QUOTE_SERVICE_SELECTION),
                'parent_id',
                $setup->getTable('quote_address'),
                'address_id'
            ),
            'parent_id',
            $setup->getTable('quote_address'),
            'address_id',
            Table::ACTION_CASCADE
        );

        $orderTable->addForeignKey(
            $setup->getFkName(
                $setup->getTable(self::TABLE_ORDER_SERVICE_SELECTION),
                'parent_id',
                $setup->getTable('sales_order_address'),
                'entity_id'
            ),
            'parent_id',
            $setup->getTable('sales_order_address'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($quoteTable);
        $setup->getConnection()->createTable($orderTable);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    public static function createServiceChargeColumns(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::QUOTE_ADDRESS_TABLE_NAME),
                self::SERVICE_CHARGE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Service Charge'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::QUOTE_ADDRESS_TABLE_NAME),
                self::SERVICE_CHARGE_BASE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Base Service Charge'
                ]
            );

        $setup->getConnection()
              ->addColumn(
                  $setup->getTable(self::QUOTE_TABLE_NAME),
                  self::SERVICE_CHARGE_FIELD_NAME,
                  [
                      'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                      'nullable' => true,
                      'length' => '12,4',
                      'default' => '0.0000',
                      'comment' => 'DHL Service Charge'
                  ]
              );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::QUOTE_TABLE_NAME),
                self::SERVICE_CHARGE_BASE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Base Service Charge'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::ORDER_TABLE_NAME),
                self::SERVICE_CHARGE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Service Charge'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::ORDER_TABLE_NAME),
                self::SERVICE_CHARGE_BASE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Base Service Charge'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::INVOICE_TABLE_NAME),
                self::SERVICE_CHARGE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Service Charge'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::INVOICE_TABLE_NAME),
                self::SERVICE_CHARGE_BASE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Base Service Charge'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::CREDITMEMO_TABLE_NAME),
                self::SERVICE_CHARGE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Service Charge'
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable(self::CREDITMEMO_TABLE_NAME),
                self::SERVICE_CHARGE_BASE_FIELD_NAME,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'DHL Base Service Charge'
                ]
            );

        $setup->endSetup();
    }
}
