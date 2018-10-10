<?php
/**
 * See LICENSE.md for license details.
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
 * @package Dhl\Shipping\Model
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
        $html = '<input type="hidden" id="%s" value="%s"/>
        <script>
            (function() {
                let checkboxes = document.querySelectorAll("[name=\'%s\']");
                let hidden = document.getElementById("%s");
                /** Make the hidden input the submitted one. **/
                hidden.name = checkboxes.item(0).name;
                checkboxes.forEach(function(checkbox) {
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
        </script>';

        return sprintf(
            $html,
            $this->getHtmlId() . self::PSEUDO_POSTFIX,
            $this->getData('value'),
            $this->getName(),
            $this->getHtmlId() . self::PSEUDO_POSTFIX
        );
    }
}
