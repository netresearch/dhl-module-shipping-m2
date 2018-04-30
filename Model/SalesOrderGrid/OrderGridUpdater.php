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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\SalesOrderGrid;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\ResourceModel\GridInterface;

/**
 * OrderGridUpdater
 *
 * @category Dhl
 * @package  Dhl\Shipping\Model\SalesOrderGrid
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class OrderGridUpdater
{
    /**
     * @var ScopeConfigInterface
     */
    private $globalConfig;

    /**
     * @var GridInterface
     */
    private $entityGrid;

    /**
     * OrderGridUpdater constructor.
     *
     * @param GridInterface $entityGrid
     * @param ScopeConfigInterface $globalConfig
     */
    public function __construct(
        GridInterface $entityGrid,
        ScopeConfigInterface $globalConfig
    ) {
        $this->globalConfig = $globalConfig;
        $this->entityGrid = $entityGrid;
    }

    /**
     * Handles synchronous updating order entity in grid.
     *
     * Works only if asynchronous grid indexing is disabled
     * in global settings.
     *
     * @param int $orderId
     * @return void
     */
    public function update($orderId)
    {
        if (!$this->globalConfig->getValue('dev/grid/async_indexing')) {
            $this->entityGrid->refresh($orderId);
        }
    }
}
