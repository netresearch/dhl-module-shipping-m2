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
class Disable extends Field
{
    const GV_API_TEXT = 'Geschäftskunden API';

    const GL_API_TEXT = 'Global Label API';

    /**
     * @var ModuleConfig
     */
    private $config;

    /**
     * Disable constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param ModuleConfig $config
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ModuleConfig $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        $element->setData('value',$this->determineApiBasedOnCountry());
        return $element->getElementHtml();
    }

    /**
     * Obtain API designation based on shipping origin country.
     *
     * @return string
     */
    private function determineApiBasedOnCountry()
    {
        $result          = self::GL_API_TEXT;
        $originCountryId = $this->config->getShipperCountry();
        $gvApiCountrys   = ['DE', 'AT'];
        if (in_array($originCountryId, $gvApiCountrys)) {
            $result = self::GV_API_TEXT;
        }

        return $result;
    }
}
