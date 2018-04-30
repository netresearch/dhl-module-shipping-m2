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
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Observer;

use Dhl\Shipping\Api\Data\ShippingInfoInterface;
use \Dhl\Shipping\Api\OrderAddressExtensionRepositoryInterface;
use Dhl\Shipping\Model\ShippingInfo\AbstractAddressExtension;
use \Dhl\Shipping\Model\ShippingInfoBuilder;
use \Dhl\Shipping\Model\ShippingInfo\OrderAddressExtensionFactory;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Sales\Api\OrderAddressRepositoryInterface;

/**
 * Update shipping info when order address was updated in admin panel.
 *
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UpdateShippingInfoObserver implements ObserverInterface
{
    /**
     * @var RequestInterface|\Magento\Framework\HTTP\PhpEnvironment\Request
     */
    private $request;

    /**
     * @var OrderAddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var OrderAddressExtensionRepositoryInterface sitoryInterface
     */
    private $addressExtensionRepository;

    /**
     * @var OrderAddressExtensionFactory
     */
    private $addressExtensionFactory;

    /**
     * @var ShippingInfoBuilder
     */
    private $shippingInfoBuilder;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * UpdateShippingInfoObserver constructor.
     *
     * @param RequestInterface $request
     * @param OrderAddressRepositoryInterface $addressRepository
     * @param OrderAddressExtensionRepositoryInterface $addressExtensionRepo
     * @param OrderAddressExtensionFactory $addressExtensionFactory
     * @param ShippingInfoBuilder $shippingInfoBuilder
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        RequestInterface $request,
        OrderAddressRepositoryInterface $addressRepository,
        OrderAddressExtensionRepositoryInterface $addressExtensionRepo,
        OrderAddressExtensionFactory $addressExtensionFactory,
        ShippingInfoBuilder $shippingInfoBuilder,
        CountryFactory $countryFactory
    ) {
        $this->request = $request;
        $this->addressRepository = $addressRepository;
        $this->addressExtensionRepository = $addressExtensionRepo;
        $this->addressExtensionFactory = $addressExtensionFactory;
        $this->shippingInfoBuilder = $shippingInfoBuilder;

        $this->countryFactory = $countryFactory;
    }

    /**
     * When an order address was updated, update additional DHL shipping information accordingly.
     *
     * Event:
     * - admin_sales_order_address_update
     *
     * @param Observer $observer
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(Observer $observer)
    {
        $dhlAddressFields = $this->request->getPostValue('shipping_info');
        if (!$dhlAddressFields) {
            return;
        }

        // load updated address
        $addressId = $this->request->getParam('address_id');
        /** @var $shippingAddress \Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Sales\Model\Order\Address */
        $shippingAddress = $this->addressRepository->get($addressId);

        // load previous info data if available
        $shippingInfo = $this->addressExtensionRepository->getShippingInfo($addressId);
        if ($shippingInfo instanceof ShippingInfoInterface) {
            $this->shippingInfoBuilder->setInfo(json_encode($shippingInfo));
        }

        // update with current address data
        $this->shippingInfoBuilder->setShippingAddress($shippingAddress);
        $this->shippingInfoBuilder->setStreet(
            $dhlAddressFields['street_name'],
            $dhlAddressFields['street_number'],
            $dhlAddressFields['address_addition']
        );

        $packstation = isset($dhlAddressFields['packstation']) ? $dhlAddressFields['packstation'] : [];
        if (!empty($packstation['packstation_number'])) {
            $this->shippingInfoBuilder->setPackstation(
                $packstation['packstation_number'],
                $shippingAddress->getPostcode(),
                $shippingAddress->getCity(),
                $shippingAddress->getCountryId(),
                isset($packstation['post_number']) ? $packstation['post_number'] : ''
            );
        } else {
            $this->shippingInfoBuilder->unsetPackstation();
        }

        $postfiliale = isset($dhlAddressFields['postfiliale']) ? $dhlAddressFields['postfiliale'] : [];
        if (!empty($postfiliale['postfilial_number']) && !empty($postfiliale['post_number'])) {
            $this->shippingInfoBuilder->setPostfiliale(
                $postfiliale['postfilial_number'],
                $postfiliale['post_number'],
                $shippingAddress->getPostcode(),
                $shippingAddress->getCity(),
                $shippingAddress->getCountryId()
            );
        } else {
            $this->shippingInfoBuilder->unsetPostfiliale();
        }

        $parcelShop = isset($dhlAddressFields['parcel_shop']) ? $dhlAddressFields['parcel_shop'] : [];
        if (!empty($parcelShop['parcel_shop_number'])) {
            $this->shippingInfoBuilder->setParcelShop(
                $parcelShop['parcel_shop_number'],
                $shippingAddress->getPostcode(),
                $shippingAddress->getCity(),
                $shippingAddress->getCountryId(),
                isset($parcelShop['street_name']) ? $parcelShop['street_name'] : '',
                isset($parcelShop['street_number']) ? $parcelShop['street_number'] : ''
            );
        } else {
            $this->shippingInfoBuilder->unsetParcelShop();
        }

        $shippingInfo = $this->shippingInfoBuilder->create();

        $addressExtension = $this->addressExtensionFactory->create(['data' => [
            AbstractAddressExtension::ADDRESS_ID => $addressId,
            AbstractAddressExtension::SHIPPING_INFO => $shippingInfo,
        ]]);

        $this->addressExtensionRepository->save($addressExtension);
    }
}
