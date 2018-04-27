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
 * @package   Dhl\Shipping
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Dhl\Shipping\Model\Adminhtml\System\Config\Source\Procedure;
use Dhl\Shipping\Util\ShippingProductsInterface;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * Dhl Shipping Form Field Html Select Block
 *
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Procedures extends Select
{
    /**
     * @var Procedure
     */
    private $source;

    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * Procedures constructor.
     *
     * @param Context $context
     * @param Procedure $source
     * @param ShippingProductsInterface $shippingProducts
     * @param array $data
     */
    public function __construct(
        Context $context,
        Procedure $source,
        ShippingProductsInterface $shippingProducts,
        array $data = []
    ) {
        $this->source = $source;
        $this->shippingProducts = $shippingProducts;

        parent::__construct($context, $data);
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
            $procedures = $this->filterAvailable($this->source->toOptionArray());
            foreach ($procedures as $procedureData) {
                $this->addOption($procedureData['value'], $this->escapeHtml($procedureData['label']));
            }
        }

        return parent::_toHtml();
    }

    /**
     * @param $data
     * @return array
     */
    private function filterAvailable($data)
    {
        $shippingOrigin = $this->_scopeConfig->getValue(
            ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
            ScopeInterface::SCOPE_WEBSITE
        );
        $availableProcedures = $this->shippingProducts->getApplicableProcedures($shippingOrigin);
        $data = array_filter($data, function ($element) use ($availableProcedures) {
            return in_array($element['value'], $availableProcedures);
        });

        return $data;
    }
}
