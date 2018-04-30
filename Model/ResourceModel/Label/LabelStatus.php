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
 * @package   Dhl\Shipping\Model
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\ResourceModel\Label;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Dhl\Shipping\Setup\ShippingSetup;

/**
 * ShippingInfoBuilder
 *
 * @package  Dhl\Shipping\Model
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class LabelStatus extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ShippingSetup::TABLE_LABEL_STATUS, 'entity_id');
    }

    /**
     * @param string $gridTableName
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getIds($gridTableName)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['order_id'])
            ->joinLeft(
                [$gridTableName => $connection->getTableName($gridTableName)],
                sprintf(
                    '%s.%s = %s.%s',
                    $this->getMainTable(),
                    'order_id',
                    $gridTableName,
                    'entity_id'
                ),
                []
            )
            ->where('status_code != dhlshipping_label_status');

        return $connection->fetchAll($select, [], \Zend_Db::FETCH_COLUMN);
    }
}
