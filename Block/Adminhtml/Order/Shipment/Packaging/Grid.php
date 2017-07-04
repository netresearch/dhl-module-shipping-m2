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

use Dhl\Shipping\Api\Config\ModuleConfigInterface;
use Dhl\Shipping\Api\Util\ShippingRoutesInterface;
use \Magento\Backend\Block\Template\Context;
use \Magento\Sales\Model\Order\Shipment\ItemFactory;
use \Magento\Framework\Registry;

/**
 * Grid
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Grid extends \Magento\Shipping\Block\Adminhtml\Order\Packaging\Grid
{
    const BCS_GRID_TEMPLATE = 'Dhl_Shipping::order/packaging/grid_bcs.phtml';

    const GL_GRID_TEMPLATE = 'Dhl_Shipping::order/packaging/grid_gl.phtml';

    const STANDARD_TEMPLATE ='Magento_Shipping::order/packaging/grid.phtml';

    /** @var  ShippingRoutesInterface */
    private $shippingRoutes;

    /** @var  ModuleConfigInterface */
    private $moduleConfig;

    /**
     * Grid constructor.
     * @param Context $context
     * @param ItemFactory $shipmentItemFactory
     * @param Registry $registry
     * @param ModuleConfigInterface $moduleConfig
     * @param ShippingRoutesInterface $shippingRoutes
     * @param array $data
     */
    public function __construct(
        Context $context,
        ItemFactory $shipmentItemFactory,
        Registry $registry,
        ModuleConfigInterface $moduleConfig,
        ShippingRoutesInterface $shippingRoutes,
        array $data = []
    ) {
        $this->shippingRoutes = $shippingRoutes;
        $this->moduleConfig = $moduleConfig;
        parent::__construct($context, $shipmentItemFactory, $registry, $data);
    }

    public function getTemplate()
    {
        $originCountryId = $this->moduleConfig->getOriginCountry($this->getShipment()->getStoreId());
        $destCountryId   = $this->getShipment()->getShippingAddress()->getCountryId();
        $euCountries     = $this->moduleConfig->getEuCountryList($this->getShipment()->getStoreId());
        $bcsCountries    = ['DE','AT'];

        $isCrossBorder = $this->shippingRoutes->isCrossBorderRoute($originCountryId, $destCountryId, $euCountries);
        $usedTemplate  = self::STANDARD_TEMPLATE;

        return self::GL_GRID_TEMPLATE;

        if ($isCrossBorder && in_array($originCountryId, $bcsCountries)) {
            $usedTemplate = self::BCS_GRID_TEMPLATE;
        } elseif ($isCrossBorder && !in_array($originCountryId, $bcsCountries)) {
            $usedTemplate = self::GL_GRID_TEMPLATE;
        }

        return $usedTemplate;
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getCountryOfOrigin($storeId)
    {
        return $this->moduleConfig->getOriginCountry($storeId);
    }
}
