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

use Dhl\Shipping\Model\Attribute\Backend\ExportDescription;
use Dhl\Shipping\Model\Attribute\Backend\TariffNumber;
use Dhl\Shipping\Model\Attribute\Source\DGCategory;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Uninstall
 *
 * @package  Dhl\Shipping\Setup
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Uninstall implements UninstallInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * Uninstall
     * @param EavSetup $eavSetup
     */
    public function __construct(EavSetup $eavSetup)
    {
        $this->eavSetup = $eavSetup;
    }

    /**
     * Remove schema and data as created during module installation.
     *
     * @param SchemaSetupInterface $schemaSetup
     * @param ModuleContextInterface $context
     */
    public function uninstall(
        SchemaSetupInterface $schemaSetup,
        ModuleContextInterface $context
    ) {
        $this->deleteShippingAddressTable($schemaSetup);
        $this->removeConfigurations($schemaSetup);
        $this->deleteServiceSelectionTables($schemaSetup);
        $this->deleteAttributes($this->eavSetup);
        $this->deleteServiceChargeColumns($schemaSetup);
    }

    /**
     * @param SchemaSetupInterface $uninstaller
     * @return void
     */
    private function deleteShippingAddressTable(SchemaSetupInterface $uninstaller)
    {
        $uninstaller->getConnection()->dropTable(ShippingSetup::TABLE_QUOTE_ADDRESS);
        $uninstaller->getConnection()->dropTable(ShippingSetup::TABLE_ORDER_ADDRESS);
    }

    /**
     * @param SchemaSetupInterface $uninstaller
     * @return void
     */
    private function removeConfigurations(SchemaSetupInterface $uninstaller)
    {
        $configTable = $uninstaller->getTable('core_config_data');
        $uninstaller->getConnection()->delete($configTable, "`path` LIKE 'carriers/dhlshipping/%'");
    }

    /**
     * @param EavSetup $uninstaller
     * @return void
     */
    private function deleteAttributes(EavSetup $uninstaller)
    {
        $uninstaller->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            DGCategory::CODE
        );
        $uninstaller->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            TariffNumber::CODE
        );
        $uninstaller->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            ExportDescription::CODE
        );
    }

    /**
     * @param SchemaSetupInterface $uninstaller
     * @return void
     */
    private function deleteServiceSelectionTables($uninstaller)
    {
        $uninstaller->getConnection()->dropTable(ShippingSetup::TABLE_ORDER_SERVICE_SELECTION);
        $uninstaller->getConnection()->dropTable(ShippingSetup::TABLE_QUOTE_SERVICE_SELECTION);
    }

    /**
     * Drop all service charge columns from core tables.
     *
     * @param SchemaSetupInterface $uninstaller
     */
    private function deleteServiceChargeColumns(SchemaSetupInterface $uninstaller)
    {
        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::QUOTE_ADDRESS_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::QUOTE_ADDRESS_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_BASE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::QUOTE_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::QUOTE_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_BASE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::ORDER_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::ORDER_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_BASE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::INVOICE_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::INVOICE_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_BASE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::CREDITMEMO_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_FIELD_NAME
        );

        $uninstaller->getConnection()->dropColumn(
            ShippingSetup::CREDITMEMO_TABLE_NAME,
            ShippingSetup::SERVICE_CHARGE_BASE_FIELD_NAME
        );
    }
}
