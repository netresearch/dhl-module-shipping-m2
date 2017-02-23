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
namespace Dhl\Versenden\Model\Plugin\Checkout;

use Dhl\Versenden\Api\Data\ShippingInfo\InfoInterface;
use \Dhl\Versenden\Webservice\ShippingInfo\Serializer;
use \Dhl\Versenden\Api\ShippingInfoRepositoryInterface;
use \Dhl\Versenden\Api\StreetSplitterInterface;
use \Dhl\Versenden\Model\ShippingInfo\QuoteShippingInfoFactory;
use \Magento\Checkout\Api\Data\ShippingInformationInterface;
use \Magento\Checkout\Model\ShippingInformationManagement;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Quote\Api\CartRepositoryInterface;

/**
 * ShippingInformationManagementPlugin
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingInformationManagementPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var QuoteShippingInfoFactory
     */
    private $quoteInfoFactory;

    /**
     * @var ShippingInfoRepositoryInterface
     */
    private $quoteInfoRepository;

    /**
     * @var InfoInterface
     */
    private $shippingInfo;

    /**
     * Address Split Helper
     *
     * @var StreetSplitterInterface
     */
    private $streetSplitter;

    /**
     * Country Model Factory
     *
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param QuoteShippingInfoFactory $quoteInfoFactory
     * @param ShippingInfoRepositoryInterface $quoteInfoRepository
     * @param InfoInterface $shippingInfo
     * @param CountryFactory $countryFactory
     * @param StreetSplitterInterface $streetSplitter
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        QuoteShippingInfoFactory $quoteInfoFactory,
        ShippingInfoRepositoryInterface $quoteInfoRepository,
        InfoInterface $shippingInfo,
        CountryFactory $countryFactory,
        StreetSplitterInterface $streetSplitter
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteInfoFactory = $quoteInfoFactory;
        $this->quoteInfoRepository = $quoteInfoRepository;
        $this->shippingInfo = $shippingInfo;
        $this->countryFactory = $countryFactory;
        $this->streetSplitter = $streetSplitter;
    }

    /**
     * Will be called, the moment, the shipping address is saved
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement   $subject
     * @param int                                                     $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     *
     * @return array|null
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $addressInformation->getShippingAddress();
        $street = $shippingAddress->getStreetFull();
        $streetParts = $this->streetSplitter->splitStreet($street);

        //TODO(nr): persist additional data from checkout in shipping info json, @see modify-shipping-information.js
//        $postalFacility = $shippingAddress->getExtensionAttributes()->getDhlshipping()->getPostalFacility();
//        $services = $shippingAddress->getExtensionAttributes()->getDhlshipping()->getServices();

        $countryDirectory = $this->countryFactory->create();
        $countryDirectory->loadByCode($shippingAddress->getCountryId());

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
            'state'           => $shippingAddress->getRegion(),
            'phone'           => $shippingAddress->getTelephone(),
            //FIXME(nr): email address is not included
            'email'           => $shippingAddress->getEmail(),
            'packstation'     => null,
            'postfiliale'     => null,
            'parcelShop'      => null,
        ];

        // load receiver data from array into shipping info (somehow awkward, needs refactoring)
        $this->shippingInfo->getReceiver()->fromArray($receiverInfo, false);
        $serializedInfo = Serializer::serialize($this->shippingInfo);

        if ($serializedInfo) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->getActive($cartId);

            // save/override versenden info into extension table
            $quoteInfo = $this->quoteInfoFactory->create(['data' => [
                'address_id' => $quote->getShippingAddress()->getId(),
                'info' => $serializedInfo,
            ]]);
            $this->quoteInfoRepository->save($quoteInfo);
        }

        return null;
    }
}
