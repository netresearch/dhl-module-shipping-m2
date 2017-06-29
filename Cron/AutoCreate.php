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
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace DHL\Shipping\Cron;

use Magento\Framework\Api\Search\SearchCriteriaFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Shipping\Model\Shipping\LabelGenerator;

class AutoCreate
{
    /**
     * @var SearchCriteriaFactory
     */
    private $searchCriteriaFactory;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * AutoCreate constructor.
     * @param LabelGenerator $labelGenerator
     * @param OrderRepository $orderRepository
     * @param SearchCriteriaFactory $searchCriteriaFactory
     */
    public function __construct(
        LabelGenerator $labelGenerator,
        OrderRepository $orderRepository,
        SearchCriteriaFactory $searchCriteriaFactory
    ) {
        $this->labelGenerator = $labelGenerator;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaFactory = $searchCriteriaFactory;
    }

    public function run()
    {
        $this->orderRepository;
    }

    private function createSearchCriteria()
    {
        $searchCriteria = $this->searchCriteriaFactory->create(
            []
        );
    }

}