<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Api\Data;

/**
 * Versenden Info Entity Interface
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface VersendenInfoOrderInterface
{
    const VERSENDEN_INFO_ID    = 'sales_order_address_id';
    const VERSENDEN_INFO_FIELD = 'dhl_versenden_info';

    /**
     * @return int
     */
    public function getSalesOrderAddressId();

    /**
     * @param int $salesOrderAddressId
     *
     * @return self
     */
    public function setSalesOrderAddressId($salesOrderAddressId);

    /**
     * @return string
     */
    public function getDhlVersendenInfo();

    /**
     * @param string $dhlVersendenInfo
     *
     * @return self
     */
    public function setDhlVersendenInfo($dhlVersendenInfo);
}
