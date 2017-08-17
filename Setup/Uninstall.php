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

use Dhl\Shipping\Model\Attribute\Source\DGCategory;
use Dhl\Shipping\Model\Attribute\Backend\TariffNumber;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Uninstall
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Uninstall implements UninstallInterface
{

    /**
     * Eav setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Remove data that was created during module installation.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $uninstaller = $setup;

        $this->deleteShippingAddressTable($uninstaller);
        $this->removeConfigurations($uninstaller);
        $this->deleteAttributes($uninstaller);
    }

    /**
     * @param $uninstaller
     */
    private function deleteShippingAddressTable($uninstaller)
    {
        $uninstaller->getConnection()
                    ->dropTable(ShippingSetup::TABLE_QUOTE_ADDRESS);
        $uninstaller->getConnection()
                    ->dropTable(ShippingSetup::TABLE_ORDER_ADDRESS);
    }

    private function removeConfigurations($uninstaller)
    {
        $configTable = $uninstaller->getTable('core_config_data');
        $uninstaller->getConnection()
                    ->delete(
                        $configTable,
                        "`path` LIKE 'carriers/dhlshipping/%'"
                    );
    }

    private function deleteAttributes($uninstaller)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $uninstaller]);
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            DGCategory::CODE
        );
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            TariffNumber::CODE
        );
    }
}
