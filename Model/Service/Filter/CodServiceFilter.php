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
 * PHP version 5
 *
 * @package   Dhl\Shipping
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Service\Filter;

use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Service\Bcs\Cod as BcsCod;
use Dhl\Shipping\Service\Bcs\PreferredLocation;
use Dhl\Shipping\Service\Bcs\PreferredNeighbour;
use Dhl\Shipping\Service\Filter\FilterInterface;
use Dhl\Shipping\Service\Gla\Cod as GlaCod;

/**
 * Check if cod is available for given service.
 *
 * @package  Dhl\Shipping\Service
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CodServiceFilter implements FilterInterface
{

    /**
     * @var array
     */
    private $selectedServices = [];

    private $codServices = [
        BcsCod::CODE,
        GlaCod::CODE,
    ];

    private $noCodServices = [
        PreferredLocation::CODE,
        PreferredNeighbour::CODE,
    ];

    /**
     * CodServiceFilter constructor.
     *
     * @param array $selectedServices
     */
    public function __construct(array $selectedServices)
    {
        $this->selectedServices = $selectedServices;
    }

    /**
     * @param ServiceInterface $service
     * @return bool
     */
    public function isAllowed(ServiceInterface $service)
    {
        if (in_array($service->getCode(), $this->codServices, true)) {
            // only run if service is actually cash on delivery service
            foreach ($this->selectedServices as $selectedService) {
                if (in_array($selectedService->getServiceCode(), $this->noCodServices, true)) {
                    return false;
                }
            }
        }

        return $service->isEnabled();
    }

    /**
     * @param array $selectedServices
     * @return \Closure
     */
    public static function create(array $selectedServices)
    {
        return function (ServiceInterface $service) use ($selectedServices) {
            $filter = new static($selectedServices);

            return $filter->isAllowed($service);
        };
    }
}
