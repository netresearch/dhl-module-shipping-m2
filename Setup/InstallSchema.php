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

use Dhl\Versenden\Api\Data\ShippingInfoInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * InstallSchema
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class InstallSchema implements InstallSchemaInterface
{
    const TABLE_QUOTE_ADDRESS = 'dhlshipping_quote_address';
    const TABLE_ORDER_ADDRESS = 'dhlshipping_order_address';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        /**
         * Create table 'dhlshipping_quote_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_QUOTE_ADDRESS)
        );

        $table->addColumn(
            ShippingInfoInterface::ADDRESS_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Quote Address Id'
        );
        $table->addColumn(ShippingInfoInterface::INFO, Table::TYPE_TEXT, null, [], 'DHL Shipping Info');
        $table->addForeignKey(
            $installer->getFkName(
                self::TABLE_QUOTE_ADDRESS,
                ShippingInfoInterface::ADDRESS_ID,
                'quote_address',
                'address_id'
            ),
            ShippingInfoInterface::ADDRESS_ID,
            $installer->getTable('quote_address'),
            'address_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'dhlshipping_order_address'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable(self::TABLE_ORDER_ADDRESS)
        );
        $table->addColumn(
            ShippingInfoInterface::ADDRESS_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Order Address Id'
        );
        $table->addColumn(ShippingInfoInterface::INFO, Table::TYPE_TEXT, null, [], 'DHL Shippin Info');
        $table->addForeignKey(
            $installer->getFkName(
                self::TABLE_ORDER_ADDRESS,
                ShippingInfoInterface::ADDRESS_ID,
                'sales_order_address',
                'entity_id'
            ),
            ShippingInfoInterface::ADDRESS_ID,
            $installer->getTable('sales_order_address'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);
    }
}
