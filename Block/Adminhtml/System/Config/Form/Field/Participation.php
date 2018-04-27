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

use Dhl\Shipping\Util\ShippingProductsInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Array configuration field with procedures and the merchant's participation number.
 * The procedures dropdown is rendered per row using a separate form field.
 * @see Procedures
 *
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Participation extends AbstractFieldArray
{
    /**
     * @var Procedures
     */
    private $templateRenderer;

    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * Participation constructor.
     *
     * @param Context $context
     * @param ShippingProductsInterface $shippingProducts
     * @param array $data
     */
    public function __construct(
        Context $context,
        ShippingProductsInterface $shippingProducts,
        array $data = []
    ) {
        $this->shippingProducts = $shippingProducts;

        parent::__construct($context, $data);
    }

    /**
     * Create renderer used for displaying the country select element
     *
     * @return Procedures|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTemplateRenderer()
    {
        if (!$this->templateRenderer) {
            $this->templateRenderer = $this->getLayout()->createBlock(
                Procedures::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->templateRenderer->setClass('procedure');
        }

        return $this->templateRenderer;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->getTemplateRenderer()->calcOptionHash($row->getData('procedure'))] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }

    /**
     * Prepare to render
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('procedure', [
            'label'    => __('Procedure'),
            'renderer' => $this->getTemplateRenderer()
        ]);

        $this->addColumn('participation', [
            'label' => __('Participation'),
            'style' => 'width:80px',
            'class' => 'validate-length maximum-length-2 minimum-length-2 validate-digits'
        ]);

        // hide "Add after" button
        $this->_addAfter = false;
    }
}
