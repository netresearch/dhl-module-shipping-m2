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
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Setup;

use Dhl\Shipping\Model\ShippingInfo\AbstractAddressExtension;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * InstallSchema
 *
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Install schema.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'dhlshipping_quote_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(ShippingSetup::TABLE_QUOTE_ADDRESS)
        );

        $table->addColumn(
            AbstractAddressExtension::ADDRESS_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Quote Address Id'
        );
        $table->addColumn(AbstractAddressExtension::INFO, Table::TYPE_TEXT, null, [], 'DHL Shipping Info');
        $table->addForeignKey(
            $installer->getFkName(
                ShippingSetup::TABLE_QUOTE_ADDRESS,
                AbstractAddressExtension::ADDRESS_ID,
                'quote_address',
                'address_id'
            ),
            AbstractAddressExtension::ADDRESS_ID,
            $installer->getTable('quote_address'),
            'address_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'dhlshipping_order_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(ShippingSetup::TABLE_ORDER_ADDRESS)
        );
        $table->addColumn(
            AbstractAddressExtension::ADDRESS_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Order Address Id'
        );
        $table->addColumn(AbstractAddressExtension::INFO, Table::TYPE_TEXT, null, [], 'DHL Shipping Info');
        $table->addForeignKey(
            $installer->getFkName(
                ShippingSetup::TABLE_ORDER_ADDRESS,
                AbstractAddressExtension::ADDRESS_ID,
                'sales_order_address',
                'entity_id'
            ),
            AbstractAddressExtension::ADDRESS_ID,
            $installer->getTable('sales_order_address'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table dhlshipping_label_status
         */
        ShippingSetup::createLabelStatusTable($setup);
    }
}
