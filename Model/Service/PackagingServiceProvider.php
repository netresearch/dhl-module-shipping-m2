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
 * @package   Dhl\Shipping\Model
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Service;

use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Api\Data\ShippingInfoInterface;
use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Service\Filter\MerchantSelectionFilter;

/**
 * Load services for packaging popup
 *
 * @package  Dhl\Shipping\Model
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PackagingServiceProvider
{
    /**
     * @var ServicePool
     */
    private $servicePool;

    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * PackagingServiceProvider constructor.
     * @param ServicePool $servicePool
     * @param ModuleConfigInterface $config
     */
    public function __construct(ServicePool $servicePool, ModuleConfigInterface $config)
    {
        $this->servicePool = $servicePool;
        $this->config = $config;
    }

    /**
     * @param ShippingInfoInterface $shippingInfo
     * @return ServiceCollection|ServiceInterface[]
     */
    public function getServices(ShippingInfoInterface $shippingInfo)
    {
        // todo(nr): load defaults from config
        $presets = [];
        $serviceCollection = $this->servicePool->getServices($presets);

        // show only services available for customers
        $filter = MerchantSelectionFilter::create();
        $serviceCollection = $serviceCollection->filter($filter);

        return $serviceCollection;
    }
}
