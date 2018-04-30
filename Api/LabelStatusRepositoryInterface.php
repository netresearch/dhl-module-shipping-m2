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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Api;

use Dhl\Shipping\Api\Data\LabelStatusInterface;
use Dhl\Shipping\Api\Data\LabelStatusSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface LabelStatusRepositoryInterface
 *
 * @package Dhl\Shipping\Api
 */
interface LabelStatusRepositoryInterface
{
    /**
     * @param LabelStatusInterface $labelStatus
     * @return LabelStatusInterface
     */
    public function save(LabelStatusInterface $labelStatus);

    /**
     * @param int $id
     * @return mixed
     */
    public function getById($id);

    /**
     * @param int $orderId
     * @return LabelStatusInterface | null
     */
    public function getByOrderId($orderId);

    /**
     * Deletes label status entity.
     *
     * @param LabelStatusInterface $labelStatus
     * @return bool
     */
    public function delete(LabelStatusInterface $labelStatus);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return LabelStatusSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
