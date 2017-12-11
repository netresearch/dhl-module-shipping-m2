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
 * @category  Dhl
 * @package   Dhl\Shipping
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Adminhtml\Order\Shipping\Address;

use \Dhl\Shipping\Api\OrderAddressExtensionRepositoryInterface;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Model\Session\Quote;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Sales\Model\AdminOrder\Create;
use \Magento\Framework\Pricing\PriceCurrencyInterface;
use \Magento\Framework\Data\FormFactory as FrameworkFormFactory;
use \Magento\Framework\Reflection\DataObjectProcessor;
use \Magento\Directory\Helper\Data;
use \Magento\Framework\Json\EncoderInterface;
use \Magento\Customer\Model\Metadata\FormFactory as CustomerFormFactory;
use \Magento\Customer\Model\Options;
use \Magento\Customer\Helper\Address;
use \Magento\Customer\Api\AddressRepositoryInterface;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Framework\Api\FilterBuilder;
use \Magento\Customer\Model\Address\Mapper;
use \Magento\Framework\Registry;

/**
 * Extended Shipping Address Form
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Address\Form
{
    /**
     * Address form template
     *
     * @var string
     */
    protected $_template = 'Magento_Sales::order/address/form.phtml';

    /**
     * @var OrderAddressExtensionRepositoryInterface
     */
    private $addressExtensionRepository;

    /**
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param FrameworkFormFactory $formFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param CustomerFormFactory $customerFormFactory
     * @param Options $options
     * @param Address $addressHelper
     * @param AddressRepositoryInterface $addressService
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Mapper $addressMapper
     * @param Registry $registry
     * @param OrderAddressExtensionRepositoryInterface $addressExtensionRepository
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        FrameworkFormFactory $formFactory,
        DataObjectProcessor $dataObjectProcessor,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        CustomerFormFactory $customerFormFactory,
        Options $options,
        Address $addressHelper,
        AddressRepositoryInterface $addressService,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        Mapper $addressMapper,
        Registry $registry,
        OrderAddressExtensionRepositoryInterface $addressExtensionRepository,
        array $data = []
    ) {
        $this->addressExtensionRepository = $addressExtensionRepository;

        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $directoryHelper,
            $jsonEncoder,
            $customerFormFactory,
            $options,
            $addressHelper,
            $addressService,
            $criteriaBuilder,
            $filterBuilder,
            $addressMapper,
            $registry,
            $data
        );
    }

    /**
     * Define form attributes (id, method, action)
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $fieldset = $this->_form->getElement('main');
        $fieldset->addType('separator', \Dhl\Shipping\Block\Adminhtml\Order\Shipping\Address\Element\Separator::class);

        $address = $this->_getAddress();

        // load previous info data
        $shippingInfo = $this->addressExtensionRepository->getShippingInfo($address->getEntityId());

        // SPLIT STREET
        $src = $this->getViewFileUrl('Dhl_Shipping::images/dhl_shipping/dhl_logo.png');
        $fieldset->addField(
            'shipping_info_street',
            'separator',
            ['value' => '<img src="' . $src . '" alt="DHL Shipping"/>']
        );

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getAddress())
            ? implode(' ', $address->getStreet())
            : $shippingInfo->getReceiver()->getAddress()->getStreetName();
        $fieldset->addField('shipping_info_street_name', 'text', [
            'name'  => "shipping_info[street_name]",
            'label' => __('Street Name'),
            'value' => $value,
        ]);

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getAddress())
            ? ''
            : $shippingInfo->getReceiver()->getAddress()->getStreetNumber();
        $fieldset->addField('shipping_info_street_number', 'text', [
            'name'  => "shipping_info[street_number]",
            'label' => __('House number'),
            'value' => $value,
        ]);

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getAddress())
            ? ''
            : $shippingInfo->getReceiver()->getAddress()->getAddressAddition();
        $fieldset->addField('shipping_info_address_addition', 'text', [
            'name'  => "shipping_info[address_addition]",
            'label' => __('Address Addition'),
            'value' => $value,
        ]);

        // PACKSTATION
        $src = $this->getViewFileUrl('Dhl_Shipping::images/dhl_shipping/icon-packStation.png');
        $fieldset->addField(
            'shipping_info_packstation',
            'separator',
            ['value' => '<img src="' . $src . '" alt="DHL Packstation"/>']
        );

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getPackstation())
            ? ''
            : $shippingInfo->getReceiver()->getPackstation()->getPackstationNumber();
        $fieldset->addField("shipping_info_packstation_number", 'text', [
            'name'  => "shipping_info[packstation][packstation_number]",
            'label' => __('Packstation Number'),
            'value' => $value,
            'class' => 'validate-number-range number-range-101-999',
        ]);

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getPackstation())
            ? ''
            : $shippingInfo->getReceiver()->getPackstation()->getPostNumber();
        $fieldset->addField("shipping_info_packstation_post_number", 'text', [
            'name'  => "shipping_info[packstation][post_number]",
            'label' => __('Post Number'),
            'value' => $value,
        ]);

        // POST OFFICE
        $src = $this->getViewFileUrl('Dhl_Shipping::images/dhl_shipping/icon-postOffice.png');
        $fieldset->addField(
            'shipping_info_postfiliale',
            'separator',
            ['value' => '<img src="' . $src . '" alt="DHL Postfiliale"/>']
        );

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getPostfiliale())
            ? ''
            : $shippingInfo->getReceiver()->getPostfiliale()->getPostfilialNumber();
        $fieldset->addField("shipping_info_postfilial_number", 'text', [
            'name'  => "shipping_info[postfiliale][postfilial_number]",
            'label' => __('Post Office Number'),
            'value' => $value,
            'class' => 'validate-number-range number-range-101-999',
        ]);

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getPostfiliale())
            ? ''
            : $shippingInfo->getReceiver()->getPostfiliale()->getPostNumber();
        $fieldset->addField("shipping_info_postfiliale_post_number", 'text', [
            'name'  => "shipping_info[postfiliale][post_number]",
            'label' => __('Post Number'),
            'value' => $value,
        ]);

        // PARCEL SHOP
        $src = $this->getViewFileUrl('Dhl_Shipping::images/dhl_shipping/icon-parcelShop.png');
        $fieldset->addField(
            'shipping_info_parcel_shop',
            'separator',
            ['value' => '<img src="' . $src . '" alt="DHL Parcel Shop"/>']
        );

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getParcelShop())
            ? ''
            : $shippingInfo->getReceiver()->getParcelShop()->getParcelShopNumber();
        $fieldset->addField("shipping_info_parcel_shop_number", 'text', [
            'name'  => "shipping_info[parcel_shop][parcel_shop_number]",
            'label' => __('Parcel Shop Number'),
            'value' => $value,
        ]);

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getParcelShop())
            ? ''
            : $shippingInfo->getReceiver()->getParcelShop()->getStreetName();
        $fieldset->addField("shipping_info_parcel_shop_street_name", 'text', [
            'name'  => "shipping_info[parcel_shop][street_name]",
            'label' => __('Street Name'),
            'value' => $value,
        ]);

        $value = (!$shippingInfo || !$shippingInfo->getReceiver() || !$shippingInfo->getReceiver()->getParcelShop())
            ? ''
            : $shippingInfo->getReceiver()->getParcelShop()->getStreetNumber();
        $fieldset->addField("shipping_info_parcel_shop_street_number", 'text', [
            'name'  => "shipping_info[parcel_shop][street_number]",
            'label' => __('Street Number'),
            'value' => $value,
        ]);

        return $this;
    }
}
