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
 * @package   Dhl\Shipping
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Controller\Adminhtml\Order;

use Dhl\Shipping\Model\CreateShipment;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\Ui\Component\MassAction\Filter;

/**
 * AutoCreate
 *
 * @package  Dhl\Shipping
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AutoCreate extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var OrderResourceInterface
     */
    private $orderResource;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CreateShipment
     */
    private $createShipment;

    /**
     * AutoCreate constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param OrderResourceInterface $orderResource
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param CreateShipment $createShipment
     */
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

    /**
     * Recieve Orders from a mass action and try to create shipments for them via the corresponding API.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     */
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
            } catch (\Exception $exception) {
                $failedShipments[$order->getIncrementId()] = $exception->getMessage();
            }
        }

        if (empty($failedShipments)) {
            $message = 'A total of %s Shipments and Labels have been created.';
            $this->messageManager->addSuccessMessage(sprintf(__($message), count($createdShipments)));
        } else {
            $this->messageManager->addWarningMessage(
                sprintf(
                    __('The label(s) for %s order(s) could not be created. %s label(s) were successfully created.'),
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
