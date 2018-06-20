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
 * @package   Dhl\Shipping\Observer
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Model\Order\ServiceSelectionFactory;
use Dhl\Shipping\Model\ResourceModel\ServiceSelectionRepository;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Persist selected services from shipment request at order address id
 *
 * @package  Dhl\Shipping\Observer
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PersistShipmentServiceSelectionObserver implements ObserverInterface
{
    /**
     * @var ServiceSelectionRepository
     */
    private $serviceSelectionRepository;

    /**
     * @var ServiceSelectionFactory
     */
    private $serviceSelectionFactory;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * PersistShipmentServiceSelectionObserver constructor.
     *
     * @param ServiceSelectionRepository $serviceSelectionRepo
     * @param ServiceSelectionFactory $serviceSelectionFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        ServiceSelectionRepository $serviceSelectionRepo,
        ServiceSelectionFactory $serviceSelectionFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->serviceSelectionRepository = $serviceSelectionRepo;
        $this->serviceSelectionFactory = $serviceSelectionFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Persist service selection with reference to an Order Address Id.
     *
     * Disabled for now because we only could persist the services
     * for the first package, which is of limited use.
     *
     * @param EventObserver $observer
     * @return ObserverInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer): ObserverInterface
    {
        return $this;

        /** @var Http $request */
        $request = $observer->getRequest();
        $orderId = $request->getParam('order_id');
        $services = $request->getParam('packages')[0]['params']['services'];
        $order = $this->orderRepository->get($orderId);

        try {
            $this->serviceSelectionRepository->deleteByOrderAddressId($order->getShippigAddressId());
        } catch (\Exception $e) {
            // do nothing
        }
        foreach ($services as $serviceCode => $serviceValues) {
            $model = $this->serviceSelectionFactory->create();
            $model->setData(
                [
                    'parent_id' => $order->getShippigAddressId(),
                    'service_code' => $serviceCode,
                    'service_value' => $serviceValues,
                ]
            );
            $this->serviceSelectionRepository->save($model);
        }

        return $this;
    }
}
