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
namespace Dhl\Shipping\Block\Adminhtml\System\Config\Form\Field\Procedure;

use \Dhl\Shipping\Model\Adminhtml\System\Config\Source\Procedure;
use \Magento\Framework\View\Element\Context;
use \Magento\Framework\View\Element\Html\Select as BaseSelect;

/**
 * Dhl Shipping Form Field Html Select Block
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Select extends BaseSelect
{
    /**
     * @var Procedure
     */
    protected $procedureModel;

    /**
     * Constructor
     *
     * @param Context   $context
     * @param Procedure $procedureModel
     * @param array     $data
     */
    public function __construct(
        Context $context,
        Procedure $procedureModel,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->procedureModel = $procedureModel;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setData('name', $value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('0', __('Select Procedure'));
            foreach ($this->procedureModel->toOptionArray() as $procedureData) {
                $this->addOption($procedureData['value'], addslashes($procedureData['label']));
            }
        }

        return parent::_toHtml();
    }
}
