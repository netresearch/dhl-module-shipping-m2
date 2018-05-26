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

use Dhl\Shipping\Api\Data\ServiceSelectionSearchResultsInterfaceFactory;
use Dhl\Shipping\Model\ResourceModel\Quote\ServiceSelectionCollection;
use Dhl\Shipping\Model\ResourceModel\Quote\ServiceSelectionCollectionFactory;
use Dhl\Shipping\Model\ResourceModel\Quote\ServiceSelection as ServiceSelectionResource;
use Dhl\Shipping\Model\Quote\ServiceSelection;
use Dhl\Shipping\Model\Quote\ServiceSelectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

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

    protected $collectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataServiceSelectionFactory;

    private $storeManager;

    public function __construct(
        ServiceSelectionResource $resource,
        ServiceSelectionFactory $serviceSelectionFactory,
        ServiceSelectionCollectionFactory $collectionFactory,
        ServiceSelectionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->testFactory = $serviceSelectionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->serviceSelectionFactory = $serviceSelectionFactory;
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
    public function save(ServiceSelection $serviceSelection) {
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
    public function getById($entityId)
    {
        $serviceSelection = $this->serviceSelectionFactory->create();
        $collection = $this->collectionFactory->create();
        $serviceSelection = $collection->getResource()->load($serviceSelection, $entityId);
        if (!$serviceSelection->getId()) {
            throw new NoSuchEntityException(__('ServiceSelection with id "%1" does not exist.', $entityId));
        }
        return $serviceSelection;
    }

    public function getByAddressId($quoteAddressId)
    {
        $collection = $this->collectionFactory->create();
        $serviceSelection = $this->serviceSelectionFactory->create();
        $collection->getResource()->load($serviceSelection, $quoteAddressId);

        return $collection;
    }
    /**
     * {@inheritdoc}
     */
    public function delete(ServiceSelection $serviceSelection) {
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


    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria) {
        $collection = $this->collectionFactory->create();

        $this->addFiltersToCollection($searchCriteria, $collection);
        $this->addSortOrdersToCollection($searchCriteria, $collection);
        $this->addPagingToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResult($searchCriteria, $collection);
    }

    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, ServiceSelectionCollection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    private function addSortOrdersToCollection(SearchCriteriaInterface $searchCriteria, ServiceSelectionCollection $collection)
    {
        foreach ((array) $searchCriteria->getSortOrders() as $sortOrder) {
            $direction = $sortOrder->getDirection() == SortOrder::SORT_ASC ? 'asc' : 'desc';
            $collection->addOrder($sortOrder->getField(), $direction);
        }
    }

    private function addPagingToCollection(SearchCriteriaInterface $searchCriteria, ServiceSelectionCollection $collection)
    {
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->setCurPage($searchCriteria->getCurrentPage());
    }

    private function buildSearchResult(SearchCriteriaInterface $searchCriteria, ServiceSelectionCollection $collection)
    {
        $searchResults = $this->searchResultsFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
