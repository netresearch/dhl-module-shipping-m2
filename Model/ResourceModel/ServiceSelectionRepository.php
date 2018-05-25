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
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\ResourceModel;

use Dhl\Shipping\Model\ResourceModel\ServiceSelection\CollectionFactory;
use Dhl\Shipping\Model\ResourceModel\ServiceSelection\ResourceModel;
use Dhl\Shipping\Model\ServiceSelection;
use Dhl\Shipping\Model\ServiceSelectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Repository for DHL Shipping Quote Address Extension
 *
 * @package  Dhl\Shipping\Model
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceSelectionRepository
{
    protected $resource;

    protected $serviceSelectionFactory;

    protected $serviceSelectionCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataServiceSelectionFactory;

    private $storeManager;

    /**
     * ServiceSelectionRepository constructor.
     *
     * @param ResourceModel $resource
     * @param ServiceSelection $serviceSelectionFactory
     * @param CollectionFactory $serviceSelectionCollectionFactory
     * @param ServiceSelectionSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceModel $resource,
        ServiceSelection $serviceSelectionFactory,
        CollectionFactory $serviceSelectionCollectionFactory,
        ServiceSelectionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->testFactory = $serviceSelectionFactory;
        $this->serviceSelectionCollectionFactory = $serviceSelectionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ServiceSelection $serviceSelection
     * @return ServiceSelection
     * @throws CouldNotSaveException
     */
    public function save(
        ServiceSelection $serviceSelection
    ) {
        try {
            $serviceSelection->getResource()->save($serviceSelection);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the test: %1',
                $exception->getMessage()
            ));
        }
        return $serviceSelection;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($serviceSelectionId)
    {
        $serviceSelection = $this->testFactory->create();
        $serviceSelection->getResource()->load($serviceSelection, $serviceSelectionId);
        if (!$serviceSelection->getId()) {
            throw new NoSuchEntityException(__('ServiceSelection with id "%1" does not exist.', $serviceSelectionId));
        }
        return $serviceSelection;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->serviceSelectionCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $fields[] = $filter->getField();
                $condition = $filter->getConditionType() ?: 'eq';
                $conditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }

        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        ServiceSelection $serviceSelection
    )
    {
        try {
            $serviceSelection->getResource()->delete($serviceSelection);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ServiceSelection: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($serviceSelectionId)
    {
        return $this->delete($this->getById($serviceSelectionId));
    }
}
