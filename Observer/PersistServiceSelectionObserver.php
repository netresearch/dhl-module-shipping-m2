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

use Dhl\Shipping\Model\Order\ServiceSelection;
use Dhl\Shipping\Model\Order\ServiceSelectionFactory;
use Dhl\Shipping\Model\ResourceModel\ServiceSelectionRepository;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

/**
 * Update shipping info when order address was updated in admin panel.
 *
 * @package  Dhl\Shipping\Observer
 * @author   Max Melzer <max.melzer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PersistServiceSelectionObserver implements ObserverInterface
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
     * PersistServiceSelectionObserver constructor.
     *
     * @param ServiceSelectionRepository $serviceSelectionRepo
     * @param ServiceSelectionFactory $serviceSelectionFactory
     */
    public function __construct(
        ServiceSelectionRepository $serviceSelectionRepo,
        ServiceSelectionFactory $serviceSelectionFactory
    ) {
        $this->serviceSelectionRepository = $serviceSelectionRepo;
        $this->serviceSelectionFactory = $serviceSelectionFactory;
    }

    /**
     * Persist service selection with reference to an Order Address ID.
     *
     * @param EventObserver $observer
     * @return ObserverInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(EventObserver $observer): ObserverInterface
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getDataByKey('order');
        /** @var Quote $quote */
        $quote = $observer->getDataByKey('quote');
        if ($order->getIsVirtual()) {
            return $this;
        }

        try {
            $quoteAddressId = $quote->getShippingAddress()->getId();
            $serviceSelection = $this->serviceSelectionRepository->getByQuoteAddressId($quoteAddressId);
        } catch (\Exception $e) {
            return $this;
        }
        foreach ($serviceSelection as $selection) {
            /** @var ServiceSelection $selection */
            $model = $this->serviceSelectionFactory->create();
            $model->setData(
                [
                    'parent_id' => $order->getShippingAddressId(),
                    'service_code' => $selection->getServiceCode(),
                    'service_value' => $selection->getServiceValue(),
                ]
            );
            $this->serviceSelectionRepository->save($model);
        }

        return $this;
    }
}
