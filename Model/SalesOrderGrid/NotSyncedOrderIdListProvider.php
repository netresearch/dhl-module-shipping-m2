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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\SalesOrderGrid;

use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;
use Dhl\Shipping\Model\ResourceModel\Label\LabelStatus;

/**
 * NotSyncedOrderIdListProvider
 *
 * @package  Dhl\Shipping\Model
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class NotSyncedOrderIdListProvider implements NotSyncedDataProviderInterface
{
    /**
     * @var LabelStatus
     */
    private $labelStatus;

    /**
     * NotSyncedOrderIdListProvider constructor.
     *
     * @param LabelStatus $labelStatus
     */
    public function __construct(LabelStatus $labelStatus)
    {
        $this->labelStatus = $labelStatus;
    }

    /**
     * @param string $mainTableName
     * @param string $gridTableName
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIds($mainTableName, $gridTableName)
    {
        return $this->labelStatus->getIds($gridTableName);
    }
}
