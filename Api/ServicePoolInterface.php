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
 * @package   Dhl\Shipping\Api
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Api;

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Model\Service\ServiceCollection;

/**
 * Central hub for all shipping services, e.g.
 * - Additional Insurance
 * - Cash on Delivery
 *
 * Each connected webservice can inject its own services.
 *
 * @package  Dhl\Shipping\Model
 * @author   Paul Siedler <paul.siedler@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface ServicePoolInterface
{

    /**
     * Application specific access keys for Cash on delivery data
     */
    const SERVICE_COD_CODE = 'cod';
    const SERVICE_COD_PROPERTY_AMOUNT = 'amount';
    const SERVICE_COD_PROPERTY_CURRENCY_CODE = 'currency_code';

    /**
     * Application specific access keys for insurance data
     */
    const SERVICE_INSURANCE_CODE = 'insurance';
    const SERVICE_INSURANCE_PROPERTY_AMOUNT = 'amount';
    const SERVICE_INSURANCE_PROPERTY_CURRENCY_CODE = 'currency_code';

    /**
     * Obtain all available services, optionally configured with presets.
     *
     * @param ServiceSettingsInterface[] $servicePresets
     * @return ServiceCollection
     */
    public function getServices(array $servicePresets = []);
}
