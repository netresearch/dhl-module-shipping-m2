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
 * @author    Max Melzer<max.melzer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\ResourceModel;

use Dhl\Shipping\Api\Data\ServiceSelectionInterface;
use Dhl\Shipping\Api\ServiceSelectionRepositoryInterface;
use Dhl\Shipping\Model\ResourceModel\Quote\Address\ServiceSelectionCollection as QuoteServiceSelectionCollection;
use Dhl\Shipping\Model\ResourceModel\Quote\Address\ServiceSelectionCollectionFactory
    as QuoteServiceSelectionCollectionFactory;
use Dhl\Shipping\Model\ResourceModel\Order\Address\ServiceSelectionCollection as OrderServiceSelectionCollection;
use Dhl\Shipping\Model\ResourceModel\Order\Address\ServiceSelectionCollectionFactory
    as OrderServiceSelectionCollectionFactory;
use Dhl\Shipping\Model\Order\ServiceSelection as OrderServiceSelection;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\AbstractModel;

/**
 * Repository for DHL Shipping Service Selections stored with either the Quote oder Order Address
 *
 * @package  Dhl\Shipping\Model
 * @author   Max Melzer<max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ServiceSelectionRepository implements ServiceSelectionRepositoryInterface
{
    /**
     * @var QuoteServiceSelectionCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var Quote\Address\ServiceSelection
     */
    private $quoteResource;

    /**
     * @var OrderServiceSelectionCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var Order\Address\ServiceSelection
     */
    private $orderResource;

    /**
     * ServiceSelectionRepository constructor.
     *
     * @param QuoteServiceSelectionCollectionFactory $quoteCollectionFactory
     * @param Quote\Address\ServiceSelection $quoteResource
     * @param OrderServiceSelectionCollectionFactory $orderCollectionFactory
     * @param Order\Address\ServiceSelection $orderResource
     */
    public function __construct(
        QuoteServiceSelectionCollectionFactory $quoteCollectionFactory,
        Quote\Address\ServiceSelection $quoteResource,
        OrderServiceSelectionCollectionFactory $orderCollectionFactory,
        Order\Address\ServiceSelection $orderResource
    ) {
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->quoteResource = $quoteResource;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderResource = $orderResource;
    }

    /**
     * @param ServiceSelectionInterface|AbstractModel $serviceSelection
     * @return ServiceSelectionInterface
     * @throws CouldNotSaveException
     */
    public function save(ServiceSelectionInterface $serviceSelection)
    {
        try {
            if (get_class($serviceSelection) === OrderServiceSelection::class) {
                $this->orderResource->save($serviceSelection);
            } else {
                $this->quoteResource->save($serviceSelection);
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the test: %1',
                $exception->getMessage()
            ));
        }

        return $serviceSelection;
    }

    /**
     * @param string $addressId
     * @return QuoteServiceSelectionCollection
     * @throws NoSuchEntityException
     */
    public function getByQuoteAddressId($addressId)
    {
        $collection = $this->quoteCollectionFactory->create();
        $collection->addFilter('parent_id', $addressId);
        if ($collection->getSize() === 0) {
            throw new NoSuchEntityException(
                __('ServiceSelection for quote address id "%1" does not exist.', $addressId)
            );
        }

        return $collection;
    }

    /**
     * @param string $addressId
     * @return OrderServiceSelectionCollection
     * @throws NoSuchEntityException
     */
    public function getByOrderAddressId($addressId)
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFilter('parent_id', $addressId);
        if ($collection->getSize() === 0) {
            throw new NoSuchEntityException(
                __('ServiceSelection for order address id "%1" does not exist.', $addressId)
            );
        }

        return $collection;
    }

    /**
     * @param ServiceSelectionInterface|AbstractModel $serviceSelection
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ServiceSelectionInterface $serviceSelection)
    {
        try {
            if (get_class($serviceSelection) === OrderServiceSelection::class) {
                $this->orderResource->delete($serviceSelection);
            } else {
                $this->quoteResource->delete($serviceSelection);
            }
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ServiceSelection: %1',
                $exception->getMessage()
            ));
        }

        return true;
    }

    /**
     * @param string $addressId
     * @throws CouldNotDeleteException
     */
    public function deleteByQuoteAddressId($addressId)
    {
        try {
            $items = $this->getByQuoteAddressId($addressId);
            foreach ($items as $item) {
                $this->delete($item);
            }
        } catch (NoSuchEntityException $e) {
            // fail silently
        }
    }

    /**
     * @param string $addressId
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteByOrderAddressId($addressId)
    {
        $items = $this->getByOrderAddressId($addressId);
        foreach ($items as $item) {
            $this->delete($item);
        }
    }
}
