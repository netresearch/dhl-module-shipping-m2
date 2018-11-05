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
 * @package   Dhl\Shipping\Observer
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Model\Config\ModuleConfigInterface;
use Dhl\Shipping\Model\Shipping\Carrier;
use Dhl\Shipping\Webservice\GatewayInterface;
use Dhl\Shipping\Webservice\ResponseType\Generic\ResponseStatusInterface;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class DeleteTrackObserver
 *
 * @package   Dhl\Shipping\Observer
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
class DeleteTrackObserver implements ObserverInterface
{
    /**
     * @var GatewayInterface
     */
    private $gateway;

    /**
     * @var ModuleConfigInterface
     */
    private $config;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * DeleteTrackObserver constructor.
     *
     * @param GatewayInterface $gateway
     * @param ModuleConfigInterface $config
     * @param EventManager $eventManager
     */
    public function __construct(
        GatewayInterface $gateway,
        ModuleConfigInterface $config,
        EventManager $eventManager
    ) {
        $this->gateway = $gateway;
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /**
     * Observer to trigger shipment cancellation on the DHL
     * api when removing a tracking id from a shipment.
     *
     * @param Observer $observer
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Track $track */
        $track = $observer->getData('track');

        if ($track->getCarrierCode() !== Carrier::CODE) {
            // some other carrier, not our business.
            return;
        }

        $shippingOrigin = $this->config->getShipperCountry($track->getShipment()->getStoreId());
        if (!in_array($shippingOrigin, ['DE', 'AT'])) {
            // avoid sending Global Label tracks to BCS API
            return;
        }

        if (!$track->getShipment()->hasShippingLabel()) {
            // shipment has no label, no need to send cancellation request
            return;
        }

        $response = $this->gateway->cancelLabels([$track->getTrackNumber()]);
        if ($response->getStatus()->getCode() === ResponseStatusInterface::STATUS_FAILURE) {
            throw new LocalizedException(__($response->getStatus()->getMessage()));
        } else {
            $this->eventManager->dispatch('dhl_delete_track_after', ['track' => $track]);
        }
    }
}
