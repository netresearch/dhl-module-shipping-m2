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
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Service;

use Dhl\Shipping\Api\Data\Service\ServiceInputInterface;
use Dhl\Shipping\Api\Data\ServiceInterface;

/**
 * Class ServiceHydrator
 *
 * @package Dhl\Shipping\Service
 */
class ServiceHydrator
{
    /**
     * @param ServiceInterface $service
     *
     * @return array
     */
    public function extract(ServiceInterface $service)
    {
        return [
            'code'      => $service->getCode(),
            'name'      => __($service->getName()),
            'sortOrder' => $service->getSortOrder(),
            'inputs'    => array_map(
                function ($input) {
                    return $this->extractInput($input);
                },
                $service->getInputs()
            ),
        ];
    }

    /**
     * @param ServiceInputInterface $input
     *
     * @return string[]
     */
    private function extractInput(ServiceInputInterface $input)
    {
        return [
            'code'            => $input->getCode(),
            'label'           => __($input->getLabel()),
            'options'         => array_map(
                function ($option) {
                    $option['label'] = __($option['label']);
                    return $option;
                },
                $input->getOptions()
            ),
            'tooltip'         => __($input->getTooltip()),
            'placeholder'     => __($input->getPlaceholder()),
            'sortOrder'       => $input->getSortOrder(),
            'validationRules' => $input->getValidationRules(),
            'inputType'       => $input->getInputType(),
            'value'           => $input->getValue(),
            'infoText'        => __($input->getInfoText())
        ];
    }
}
