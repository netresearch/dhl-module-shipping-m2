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
 * @package   Dhl\Shipping\Setup
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * @package  Dhl\Shipping\Setup
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Eav setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var SerializerInterface
     */
    public $serializer;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        SerializerInterface $serializer
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->serializer = $serializer;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            ShippingSetup::addDangerousGoodsCategoryAttribute($eavSetup);
        }
        if (version_compare($context->getVersion(), '0.2.1', '<')) {
            ShippingSetup::addTariffNumberAttribute($eavSetup);
        }
        if (version_compare($context->getVersion(), '0.6.0', '<')) {
            ShippingSetup::convertSerializedToJson($this->scopeConfig, $this->configWriter, $this->serializer);
        }
        if (version_compare($context->getVersion(), '0.9.0', '<')) {
            ShippingSetup::addExportDescriptionAttribute($eavSetup);
        }
    }
}
