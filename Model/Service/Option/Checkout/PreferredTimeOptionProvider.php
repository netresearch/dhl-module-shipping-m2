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

namespace Dhl\Shipping\Model\Service\Option\Checkout;

use Dhl\Shipping\Api\Data\ServiceSettingsInterface;
use Dhl\Shipping\Model\Service\Option\OptionProviderInterface;
use Dhl\Shipping\Model\Service\StartDate;
use Dhl\Shipping\Service\Bcs\PreferredTime;
use Dhl\Shipping\Webservice\ParcelManagement;

/**
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PreferredTimeOptionProvider implements OptionProviderInterface
{

    const POSTAL_CODE = 'postalCode';

    const SERVICE_CODE = PreferredTime::CODE;

    /**
     * @var ParcelManagement
     */
    private $parcelManagement;

    /**
     * @var StartDate
     */
    private $startDateModel;

    /**
     * @var string[]
     */
    private $nonOption = [
        'label' => 'no time',
        'value' => '',
        'disable' => false,
    ];

    /**
     * PreferredTimeOptionProvider constructor.
     *
     * @param ParcelManagement $parcelManagement
     * @param StartDate $startDateModel
     */
    public function __construct(ParcelManagement $parcelManagement, StartDate $startDateModel)
    {
        $this->parcelManagement = $parcelManagement;
        $this->startDateModel = $startDateModel;
    }

    /**
     * @param string[] $service
     * @param string[] $args
     * @return string[]
     * @throws \Exception
     */
    public function enhanceServiceWithOptions($service, $args)
    {
        $storeId = isset($args[self::ARGUMENT_STORE]) ? $args[self::ARGUMENT_STORE] : null;
        $startDate = $this->startDateModel->getStartDate($storeId);
        $options = $this->parcelManagement->getPreferredTimeOptions($startDate, $args[self::POSTAL_CODE], $storeId);
        $options = array_merge([$this->nonOption], $options);

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
