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
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Adminhtml\System\Config\Serialized;

use \Magento\Framework\App\Config\Data\ProcessorInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Config\Value;

/**
 * Save and load config data array in JSON format.
 *
 * Conversion is also supposed to happen in M2.1 environments where the core
 * converter and serializer classes do not yet exist.
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ArraySerialized extends Value implements ProcessorInterface
{
    /**
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            $this->setValue(empty($value) ? false : $this->processValue($value));
        }
    }

    /**
     * Unset array element with '__empty' key
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }

        if (!empty($value)) {
            $value = json_encode($value);
        } else {
            $value = $this->getOldValue();
        }

        $this->setValue($value);

        parent::beforeSave();

        return $this;
    }

    /**
     * Process config value
     *
     * @param string $value
     *
     * @return string
     */
    public function processValue($value)
    {
        $result = json_decode($value, true);
        return $result;
    }

    /**
     * Get old value from config, encode for comparison with new value.
     *
     * @see \Magento\Framework\App\Config\Value::afterSave
     * @see \Magento\Framework\App\Config\Value::isValueChanged
     *
     * @return string
     */
    public function getOldValue()
    {
        $oldValue = $this->_config->getValue(
            $this->getPath(),
            $this->getScope() ?: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->getScopeCode()
        );

        if (is_array($oldValue)) {
            $oldValue = json_encode($oldValue);
        }

        return $oldValue;
    }
}
