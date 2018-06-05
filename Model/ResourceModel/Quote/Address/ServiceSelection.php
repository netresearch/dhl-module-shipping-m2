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
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\ResourceModel\Quote\Address;

use Dhl\Shipping\Setup\ShippingSetup;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource Model for DHL Shipping Quote Address Extension
 *
 * @package  Dhl\Shipping\Model
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceSelection extends AbstractDb
{
    /**
     * Resource initialization.
     */
    protected function _construct()
    {
        $this->_init(ShippingSetup::TABLE_QUOTE_SERVICE_SELECTION, 'entity_id');
    }

    /**
     * JSON encode array property
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this|AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $value = $object->getData('service_value');
        $object->setData('service_value', json_encode($value));

        return parent::_beforeSave($object);
    }

    /**
     * JSON decode array property
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this|AbstractDb
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $value = $object->getData('service_value');
        $object->setData('service_value', json_decode($value));

        return parent::_afterLoad($object);
    }
}
