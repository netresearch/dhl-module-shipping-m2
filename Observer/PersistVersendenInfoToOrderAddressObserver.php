<?php
/**
 * Dhl Versenden
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
 * @package   Dhl\Versenden
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Observer;

use \Dhl\Versenden\Api\VersendenInfoOrderRepositoryInterface;
use \Dhl\Versenden\Api\VersendenInfoQuoteRepositoryInterface;
use \Dhl\Versenden\Model\VersendenInfoOrderFactory;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * PersistVersendenInfoToOrderAddressObserver
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PersistVersendenInfoToOrderAddressObserver implements ObserverInterface
{
    /**
     * @var VersendenInfoOrderRepositoryInterface
     */
    private $versendenInfoOrderRepository;

    /**
     * Versenden Info Order Entity
     *
     * @var VersendenInfoOrderFactory
     */
    private $versendenInfoOrderFactory;

    /**
     * @var VersendenInfoQuoteRepositoryInterface
     */
    private $versendenInfoQuoteRepository;

    /**
     * PersistVersendenInfoToOrderAddressObserver constructor.
     *
     * @param VersendenInfoOrderRepositoryInterface $versendenInfoOrderRepository
     * @param VersendenInfoOrderFactory             $versendenInfoOrderFactory
     * @param VersendenInfoQuoteRepositoryInterface $versendenInfoQuoteRepository
     */
    public function __construct(
        VersendenInfoOrderRepositoryInterface $versendenInfoOrderRepository,
        VersendenInfoOrderFactory $versendenInfoOrderFactory,
        VersendenInfoQuoteRepositoryInterface $versendenInfoQuoteRepository
    )
    {
        $this->versendenInfoOrderRepository = $versendenInfoOrderRepository;
        $this->versendenInfoOrderFactory    = $versendenInfoOrderFactory;
        $this->versendenInfoQuoteRepository = $versendenInfoQuoteRepository;
    }

    /**
     * When a new order is placed, save the DHL Versenden Information, if exist, into the extension table
     *
     * Event:
     * - sales_model_service_quote_submit_success
     *
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface|\Magento\Sales\Model\Order $order */
        $order                  = $observer->getEvent()->getData('order');
        $quoteShippingAddressId = $observer->getEvent()->getData('quote')->getShippingAddress()->getId();

        try {
            $versendenInfoQuote = $this->versendenInfoQuoteRepository->get($quoteShippingAddressId);

            $versendenInfo = $this->versendenInfoOrderFactory
                ->create()
                ->setDhlVersendenInfo($versendenInfoQuote->getDhlVersendenInfo())
                ->setSalesOrderAddressId($order->getShippingAddress()->getId());
            $this->versendenInfoOrderRepository->save($versendenInfo);

        } catch (NoSuchEntityException $e) {
        }
    }
}
