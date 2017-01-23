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
 * @package   Dhl\Versenden
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * UpdateCarrierObserver
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class InstallSchema implements InstallSchemaInterface
{
    const TABLE_VERSENDEN_INFO_QUOTE    = 'versenden_quote_address';
    const TABLE_VERSENDEN_INFO_QUOTE_PK = 'quote_address_id';

    const TABLE_VERSENDEN_INFO_SALES_ORDER    = 'versenden_sales_order_address';
    const TABLE_VERSENDEN_INFO_SALES_ORDER_PK = 'sales_order_address_id';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        /**
         * Create table 'versenden_quote_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_VERSENDEN_INFO_QUOTE)
        )->addColumn(
            self::TABLE_VERSENDEN_INFO_QUOTE_PK,
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Quote Address Id'
        )->addColumn(
            'dhl_versenden_info',
            Table::TYPE_TEXT,
            null,
            [],
            'DHL Versenden Info'
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_VERSENDEN_INFO_QUOTE,
                self::TABLE_VERSENDEN_INFO_QUOTE_PK,
                'quote_address',
                'address_id'
            ),
            self::TABLE_VERSENDEN_INFO_QUOTE_PK,
            $installer->getTable('quote_address'),
            'address_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );;
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'versenden_sales_order_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_VERSENDEN_INFO_SALES_ORDER)
        )->addColumn(
            self::TABLE_VERSENDEN_INFO_SALES_ORDER_PK,
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Sales Order Address Id'
        )->addColumn(
            'dhl_versenden_info',
            Table::TYPE_TEXT,
            null,
            [],
            'DHL Versenden Info'
        )->addForeignKey(
            $installer->getFkName(
                self::TABLE_VERSENDEN_INFO_SALES_ORDER,
                self::TABLE_VERSENDEN_INFO_SALES_ORDER_PK,
                'sales_order_address',
                'entity_id'
            ),
            self::TABLE_VERSENDEN_INFO_SALES_ORDER_PK,
            $installer->getTable('sales_order_address'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );;
        $installer->getConnection()->createTable($table);

        $setup->endSetup();
    }
}
