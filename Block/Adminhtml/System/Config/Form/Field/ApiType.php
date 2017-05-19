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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Dhl\Shipping\Model\Config\ModuleConfig;

/**
 * Dhl Shipping Disable Form Field Block
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ApiType extends Field
{
    const API_TYPE_GV = 'Geschäftskundenversand API';
    const API_TYPE_GL = 'Global Label API';

    /**
     * @var ModuleConfig
     */
    private $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Config\Model\Config\ScopeDefiner
     */
    private $scopeDefiner;

    /**
     * ApiType constructor.
     * @param Context $context
     * @param ModuleConfig $config
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner
     * @param array $data
     */
    public function __construct(
        Context $context,
        ModuleConfig $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\Config\ScopeDefiner $scopeDefiner,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->scopeDefiner = $scopeDefiner;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled(true);
        $element->setData('is_disable_inheritance', true);

        $shippingOrigin = $this->scopeConfig->getValue('shipping/origin/country_id', $this->scopeDefiner->getScope());
        switch ($shippingOrigin) {
            case 'DE':
            case 'AT':
                $element->setData('value', self::API_TYPE_GV);
                break;
            default:
                $element->setData('value', self::API_TYPE_GL);
        }

        return $element->getElementHtml();
    }
}
