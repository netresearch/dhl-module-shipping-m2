<?php
/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 03.04.18
 * Time: 14:23
 */

namespace Dhl\Shipping\Controller\Adminhtml\Order;

use Dhl\Shipping\Model\CreateShipment;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\Ui\Component\MassAction\Filter;

class AutoCreate extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var OrderResourceInterface */
    private $orderResource;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CreateShipment
     */
    private $createShipment;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        OrderResourceInterface $orderResource,
        CollectionFactory $collectionFactory,
        Filter $filter,
        CreateShipment $createShipment
    ) {
        $this->orderResource = $orderResource;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->createShipment = $createShipment;
            parent::__construct($context);
    }

    public function execute()
    {
        $failedShipments = [];
        $createdShipments = [];

        $collection = $this->filter->getCollection($this->collectionFactory->create());

        /** @var Order $order */
        foreach ($collection as $order) {
            try {
                $this->createShipment->create($order);
                $createdShipments[] = $order->getId();
            } catch (LocalizedException $exception) {
                $failedShipments[$order->getIncrementId()] = $exception->getMessage();
            }
        }

        if (empty($failedShipments)) {
            $this->messageManager->addSuccessMessage(
                sprintf(__('A total of %s have been created.'), count($createdShipments))
            );
        } else {
            $this->messageManager->addWarningMessage(
                sprintf(
                    __('The labels for %s order(s) could not be created. %s labels were successfully created.'),
                    count($failedShipments),
                    count($createdShipments)
                )
            );
        }

        foreach ($failedShipments as $id => $error) {
            $this->messageManager->addErrorMessage("ID $id: $error");
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('sales/order');
    }
}
