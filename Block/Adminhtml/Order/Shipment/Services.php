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
namespace Dhl\Shipping\Block\Adminhtml\Order\Shipment;

use Dhl\Shipping\Model\Config\BcsService;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;

/**
 * Services
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Services extends \Magento\Backend\Block\Template
{
    const BCS_SERVICES_TEMPLATE = 'Dhl_Shipping::order/packaging/popup_services_bcs.phtml';

    const GL_SERVICES_TEMPLATE = '';

    /**
     * @var ModuleConfigInterface
     */
    private $moduleConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var BcsService
     */
    private $bcsServices;

    /**
     * Services constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ModuleConfigInterface $moduleConfig
     * @param BcsService $bcsService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Template\Context $context,
        ModuleConfigInterface $moduleConfig,
        BcsService $bcsService,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->moduleConfig = $moduleConfig;
        $this->bcsServices = $bcsService;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->coreRegistry->registry('current_shipment');
    }

    /**
     *
     * @return string
     */
    public function getTemplate()
    {
        $bcsCountries = ['DE', 'AT'];
        $usedTemplate = self::BCS_SERVICES_TEMPLATE;
        $originCountryId = $this->moduleConfig->getShipperCountry($this->getShipment()->getStoreId());

        if (!in_array($originCountryId, $bcsCountries)) {
            $usedTemplate = self::GL_SERVICES_TEMPLATE;
        }

        return $usedTemplate;
    }

    /**
     * @return \Dhl\Shipping\Service\ServiceCollection
     */
    public function getServiceCollection()
    {
        return $this->bcsServices->getServices($this->getShipment()->getStoreId());
    }
}
