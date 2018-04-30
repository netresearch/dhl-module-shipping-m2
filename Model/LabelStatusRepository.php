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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model;

use Dhl\Shipping\Api\Data\LabelStatusSearchResultsInterface;
use Dhl\Shipping\Api\Data\LabelStatusSearchResultsInterfaceFactory;
use Dhl\Shipping\Api\LabelStatusRepositoryInterface;
use Dhl\Shipping\Api\Data\LabelStatusInterface;
use Dhl\Shipping\Api\Data\LabelStatusInterfaceFactory;
use Dhl\Shipping\Model\ResourceModel\Label\LabelStatus as LabelStatusResourceModel;
use Dhl\Shipping\Model\ResourceModel\Label\LabelStatus;
use Dhl\Shipping\Model\ResourceModel\Label\Status\Collection;
use Dhl\Shipping\Model\ResourceModel\Label\Status\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\AbstractModel;

/**
 * Class LabelStatusRepository
 *
 * @package Dhl\Shipping\Model
 */
class LabelStatusRepository implements LabelStatusRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LabelStatusResourceModel
     */
    private $resource;

    /**
     * @var LabelStatusInterfaceFactory
     */
    private $labelStatusFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var LabelStatusSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * LabelStatusRepository constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param LabelStatusInterfaceFactory $labelStatusFactory
     * @param LabelStatus $resourceModel
     * @param CollectionProcessorInterface $collectionProcessor
     * @param LabelStatusSearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        LabelStatusInterfaceFactory $labelStatusFactory,
        LabelStatusResourceModel $resourceModel,
        CollectionProcessorInterface $collectionProcessor,
        LabelStatusSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->labelStatusFactory = $labelStatusFactory;
        $this->resource = $resourceModel;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param LabelStatusInterface|AbstractModel $labelStatus
     * @return LabelStatusInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(LabelStatusInterface $labelStatus)
    {
        try {
            $this->resource->save($labelStatus);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $labelStatus;
    }

    /**
     * @param $id
     * @return LabelStatusInterface
     */
    public function getById($id)
    {
        $labelStatus = $this->labelStatusFactory->create();
        $this->resource->load($labelStatus, $id);

        return $labelStatus;
    }

    /**
     * @param $orderId
     * @return string|null
     */
    public function getByOrderId($orderId)
    {
        /** @var \Dhl\Shipping\Model\Label\LabelStatus $labelStatus */
        $labelStatus = $this->labelStatusFactory->create();
        $this->resource->load($labelStatus, $orderId, 'order_id');
        return $labelStatus->getEntityId() ? $labelStatus : null;
    }

    /**
     * @param LabelStatusInterface $labelStatus
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(LabelStatusInterface $labelStatus)
    {
        try {
            $this->resource->delete($labelStatus);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return LabelStatusSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /**
         * @var Collection $collection
         */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /**
         * @var LabelStatusSearchResultsInterface $searchResults
         */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
