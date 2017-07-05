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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Checkout;

use \Dhl\Shipping\Model\ShippingInfo\CheckoutInfoInterface;
use \Magento\Framework\Model\AbstractModel;

/**
 * CheckoutInfo
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class CheckoutInfo extends AbstractModel implements CheckoutInfoInterface
{
    /**
     *
     * @return string[]
     */
    public function getServices()
    {
        return $this->getDataByKey(self::SERVICES);
    }

    /**
     * @param string[] $services
     * @return self
     */
    public function setServices(array $services)
    {
        $this->setData(self::SERVICES, $services);
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalFacility()
    {
        return $this->getDataByKey(self::POSTAL_FACILITY);
    }

    /**
     * @param string $postalFacility
     * @return self
     */
    public function setPostalFacility($postalFacility)
    {
        $this->setData(self::POSTAL_FACILITY, $postalFacility);
        return $this;
    }
}
