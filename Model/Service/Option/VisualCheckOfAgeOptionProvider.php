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

namespace Dhl\Shipping\Model\Service\Option;

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\Service\VisualCheckOfAge as VisualCheckOfAgeOptions;
use Dhl\Shipping\Service\Bcs\VisualCheckOfAge;

/**
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class VisualCheckOfAgeOptionProvider implements OptionProviderInterface
{

    const SERVICE_CODE = VisualCheckOfAge::CODE;

    /**
     * @param string[] $service
     * @param string[] $args
     * @return string[]
     */
    public function enhanceServiceWithOptions($service, $args)
    {
        $options = [
            [
                'label' => VisualCheckOfAgeOptions::OPTION_A16,
                'value' => VisualCheckOfAgeOptions::OPTION_A16,
            ],
            [
                'label' => VisualCheckOfAgeOptions::OPTION_A18,
                'value' => VisualCheckOfAgeOptions::OPTION_A18,
            ],
        ];

        $service[ServiceSettingsInterface::OPTIONS] = $options;

        return $service;
    }

    /**
     * @return string
     */
    public function getServiceCode()
    {
        return self::SERVICE_CODE;
    }
}
