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
namespace Dhl\Versenden\Model\Plugin;

use \Dhl\Versenden\Api\VersendenInfoQuoteRepositoryInterface;
use \Dhl\Versenden\Bcs\Api\Info\SerializerFactory;
use \Dhl\Versenden\Bcs\Api\Data\InfoInterface;
use \Dhl\Versenden\Helper\Address;
use \Dhl\Versenden\Model\VersendenInfoQuoteFactory;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Quote\Api\CartRepositoryInterface;

/**
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AddVersendenInfoToQuoteAddress
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Versenden Info Quote Entity
     *
     * @var VersendenInfoQuoteFactory
     */
    private $versendenInfoQuoteFactory;

    /**
     * Versenden Info Quote Entity Repository
     *
     * @var VersendenInfoQuoteRepositoryInterface
     */
    private $versendenInfoQuoteRepository;

    /**
     * Versenden Info Entity
     *
     * @var InfoInterface
     */
    private $infoEntity;

    /**
     * Versenden Info Entity Serializer Factory
     *
     * @var SerializerFactory
     */
    private $serializerFactory;

    /**
     * Address Split Helper
     *
     * @var Address
     */
    private $addressHelper;

    /**
     * Country Model Factory
     *
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @param CartRepositoryInterface               $quoteRepository
     * @param VersendenInfoQuoteFactory             $versendenInfoQuoteFactory
     * @param VersendenInfoQuoteRepositoryInterface $versendenInfoQuoteRepository
     * @param InfoInterface                         $infoEntity
     * @param SerializerFactory                     $serializerFactory
     * @param CountryFactory                        $countryFactory
     * @param Address                               $addressHelper
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        VersendenInfoQuoteFactory $versendenInfoQuoteFactory,
        VersendenInfoQuoteRepositoryInterface $versendenInfoQuoteRepository,
        InfoInterface $infoEntity,
        SerializerFactory $serializerFactory,
        CountryFactory $countryFactory,
        Address $addressHelper
    ) {
        $this->quoteRepository              = $quoteRepository;
        $this->versendenInfoQuoteFactory    = $versendenInfoQuoteFactory;
        $this->versendenInfoQuoteRepository = $versendenInfoQuoteRepository;
        $this->infoEntity                   = $infoEntity;
        $this->serializerFactory            = $serializerFactory;
        $this->countryFactory               = $countryFactory;
        $this->addressHelper                = $addressHelper;
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
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getShippingAddress();

        $versendenInfo = $this->infoEntity;
        $country       = $this->countryFactory->create()->loadByCode($shippingAddress->getCountryId());
        $street        = $this->addressHelper->splitStreet(implode(', ', $shippingAddress->getStreet()));
        $name          = $shippingAddress->getFirstname()
            . ' ' . $shippingAddress->getMiddlename()
            . ' ' . $shippingAddress->getLastname();

        $receiverInfo = [
            'name1'           => $name,
            'name2'           => $shippingAddress->getCompany(),
            'streetName'      => $street['street_name'],
            'streetNumber'    => $street['street_number'],
            'addressAddition' => $street['supplement'],
            'zip'             => $shippingAddress->getPostcode(),
            'city'            => $shippingAddress->getCity(),
            'country'         => $country->getName(),
            'countryISOCode'  => $country->getData('iso2_code'),
            'state'           => $shippingAddress->getRegion(),
            'phone'           => $shippingAddress->getTelephone(),
            'email'           => $shippingAddress->getEmail(),
            'packstation'     => null,
            'postfiliale'     => null,
            'parcelShop'      => null,
        ];
        $versendenInfo->getReceiver()->fromArray($receiverInfo, false);
        $versendenInfo = $this->serializerFactory->create()->serialize($versendenInfo);

        if ($versendenInfo) {
            $quoteAddressId = $this->quoteRepository->getActive($cartId)->getShippingAddress()->getId();

            // save/override versenden info into extension table
            $versendenInfo = $this->versendenInfoQuoteFactory->create()
                                                             ->setDhlVersendenInfo($versendenInfo)
                                                             ->setQuoteAddressId($quoteAddressId);
            $this->versendenInfoQuoteRepository->save($versendenInfo);
        }

        return null;
    }
}
