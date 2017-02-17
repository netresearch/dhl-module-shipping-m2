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

use \Dhl\Versenden\Api\Info\Serializer;
use \Dhl\Versenden\Api\ShippingInfoRepositoryInterface;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Sales\Api\OrderAddressRepositoryInterface;

/**
 * Update shipping info when order address was updated in admin panel.
 *
 * @category Dhl
 * @package  Dhl\Versenden
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
     * @var ShippingInfoRepositoryInterface
     */
    private $orderInfoRepository;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * UpdateShippingInfoObserver constructor.
     *
     * @param RequestInterface $request
     * @param OrderAddressRepositoryInterface $addressRepository
     * @param ShippingInfoRepositoryInterface $orderInfoRepository
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        RequestInterface $request,
        OrderAddressRepositoryInterface $addressRepository,
        ShippingInfoRepositoryInterface $orderInfoRepository,
        CountryFactory $countryFactory
    ) {
        $this->request = $request;
        $this->addressRepository = $addressRepository;
        $this->orderInfoRepository = $orderInfoRepository;
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
     */
    public function execute(Observer $observer)
    {
        $dhlAddressFields = $this->request->getPostValue('versenden_info');
        if (!$dhlAddressFields) {
            return;
        }

        // load updated address
        $addressId = $this->request->getParam('address_id');
        /** @var $shippingAddress \Magento\Sales\Api\Data\OrderAddressInterface|\Magento\Sales\Model\Order\Address */
        $shippingAddress = $this->addressRepository->get($addressId);

        // load previous info data
        $dhlOrderInfo = $this->orderInfoRepository->getById($addressId);
        $serializedInfo = $dhlOrderInfo->getInfo();
        /** @var \Dhl\Versenden\Api\Info $shippingInfo */
        $shippingInfo = \Dhl\Versenden\Api\Info::fromJson($serializedInfo);

        // update with current address data
        $streetParts = [
            'street_name' => $dhlAddressFields['street_name'],
            'street_number' => $dhlAddressFields['street_number'],
            'supplement' => $dhlAddressFields['address_addition'],
        ];

        $countryDirectory = $this->countryFactory->create();
        $countryDirectory->loadByCode($shippingAddress->getCountryId());
        $regionData = $countryDirectory->getLoadedRegionCollection()->walk('getName');

        $receiverInfo = [
            'name1'           => $shippingAddress->getName(),
            'name2'           => $shippingAddress->getCompany(),
            'streetName'      => $streetParts['street_name'],
            'streetNumber'    => $streetParts['street_number'],
            'addressAddition' => $streetParts['supplement'],
            'zip'             => $shippingAddress->getPostcode(),
            'city'            => $shippingAddress->getCity(),
            'country'         => $countryDirectory->getName(),
            'countryISOCode'  => $countryDirectory->getData('iso2_code'),
            'state'           => $regionData[$shippingAddress->getRegionId()],
            'phone'           => $shippingAddress->getTelephone(),
            'email'           => $shippingAddress->getEmail(),
            'packstation'     => null,
            'postfiliale'     => null,
            'parcelShop'      => null,
        ];

        // load receiver data from array into shipping info (somehow awkward, needs refactoring)
        $shippingInfo->getReceiver()->fromArray($receiverInfo, false);
        $serializedInfo = Serializer::serialize($shippingInfo);

        if ($serializedInfo) {
            $dhlOrderInfo->setInfo($serializedInfo);
            $this->orderInfoRepository->save($dhlOrderInfo);
        }
    }
}
