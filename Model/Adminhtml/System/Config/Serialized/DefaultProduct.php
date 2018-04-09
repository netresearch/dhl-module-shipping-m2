<?php
/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 09.04.18
 * Time: 12:30
 */

namespace Dhl\Shipping\Model\Adminhtml\System\Config\Serialized;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;

class DefaultProduct extends Value
{

    public function beforeSave()
    {
        $groups = $this->getData('groups');
        $value = $groups['dhlshipping']['fields'][$this->getData('field')];

        if (is_array($value)) {
            unset($value['__empty']);
        }

        if (!empty($value)) {
            $value = json_encode($value);
        }

        $this->setValue($value);

        parent::beforeSave();

        return $this;
    }

    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            $this->setValue(empty($value) ? false : $this->processValue($value));
        }
    }

    public function processValue($value)
    {
        $result = json_decode($value, true);
        return $result;
    }

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
