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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Data\Form\Element;

use Magento\Framework\Data\Form\Element\Checkboxes;

/**
 * Class Checkbox
 *
 * Implementation of a checkbox set input element that works inside the Magento system configuration and mimics a
 * multiselect, concatenating the values of all selected options separated with a comma inside a hidden input.
 * Used by entering the class name into the "type" attribute of a system.xml field element.
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Checkboxset extends Checkboxes
{
    const PSEUDO_POSTFIX = '_hidden'; // used to create the hidden input id.

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $this->setData('after_element_html', $this->getAfterHtml());

        return parent::getElementHtml();
    }

    /**
     * Add a hidden input whose value is kept in sync with the checked status of the checkboxes
     *
     * @return string
     */
    private function getAfterHtml()
    {
        $html = <<<HTML
<input type="hidden" id="%s" value="%s"/>
<script>
    (function () {
        let checkboxes = document.querySelectorAll("[name=\'%s\']");
        let hidden = document.getElementById("%s");
        /** Make the hidden input the submitted one. **/
        hidden.name = checkboxes.item(0).name;
        checkboxes.forEach(function (checkbox) {
            checkbox.name = "";
            let values = hidden.value.split(",");
            if (values.includes(checkbox.value)) {
                checkbox.checked = true;
            }
            /** keep the hidden input value in sync with the checkboxes. **/
            checkbox.addEventListener("change", function (event) {
                let checkbox = event.target;
                let values = hidden.value.split(",");
                if (checkbox.checked && !values.includes(checkbox.value)) {
                    values.push(checkbox.value);
                } else if (!checkbox.checked && values.includes(checkbox.value)) {
                    values.splice(values.indexOf(checkbox.value), 1)
                }
                hidden.value = values.filter(Boolean).join();
            });
        });
    })();
</script>
HTML;

        return sprintf(
            $html,
            $this->getHtmlId() . self::PSEUDO_POSTFIX,
            $this->getData('value'),
            $this->getName(),
            $this->getHtmlId() . self::PSEUDO_POSTFIX
        );
    }
}
