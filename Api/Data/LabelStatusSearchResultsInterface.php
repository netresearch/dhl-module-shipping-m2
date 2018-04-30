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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Api\Data;

use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Interface LabelStatusSearchResultsInterface
 *
 * @package Dhl\Shipping\Api
 */
interface LabelStatusSearchResultsInterface extends SearchResultInterface
{
    /**
     * @return \Magento\Framework\Api\Search\DocumentInterface[]
     */
    public function getItems();

    /**
     * @param array|null $items
     * @return $this
     */
    public function setItems(array $items = null);
}
