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
namespace Dhl\Versenden\Block\Adminhtml\Order\Shipping\Address;

use \Dhl\Versenden\Model\ShippingInfo\OrderShippingInfoRepository;
use \Dhl\Versenden\Webservice\ShippingInfo\Info;
use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Model\Session\Quote;
use \Magento\Framework\Data\Form\Element\Fieldset;
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
 * @package  Dhl\Versenden
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
     * @var OrderShippingInfoRepository
     */
    private $orderInfoRepository;

    /**
     * @param Context                      $context
     * @param Quote                        $sessionQuote
     * @param Create                       $orderCreate
     * @param PriceCurrencyInterface       $priceCurrency
     * @param FrameworkFormFactory         $formFactory
     * @param DataObjectProcessor          $dataObjectProcessor
     * @param Data                         $directoryHelper
     * @param EncoderInterface             $jsonEncoder
     * @param CustomerFormFactory          $customerFormFactory
     * @param Options                      $options
     * @param Address                      $addressHelper
     * @param AddressRepositoryInterface   $addressService
     * @param SearchCriteriaBuilder        $criteriaBuilder
     * @param FilterBuilder                $filterBuilder
     * @param Mapper                       $addressMapper
     * @param Registry                     $registry
     * @param OrderShippingInfoRepository  $orderInfoRepository
     * @param array                        $data
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
        OrderShippingInfoRepository $orderInfoRepository,
        array $data = []
    ) {
        $this->orderInfoRepository = $orderInfoRepository;

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
        $fieldset->addType('separator', \Dhl\Versenden\Block\Adminhtml\Order\Shipping\Address\Element\Separator::class);

        $address = $this->_getAddress();

        // load previous info data
        $dhlOrderInfo = $this->orderInfoRepository->getById($address->getEntityId());
        $serializedInfo = $dhlOrderInfo->getInfo();
        /** @var Info $shippingInfo */
        $shippingInfo = Info::fromJson($serializedInfo);

        // add info deserialized data to fieldset
        $receiverData = $shippingInfo->getReceiver()->toArray();
        $this->_prepareAddressFields($fieldset, $receiverData);

        return $this;
    }

    /**
     * @param Fieldset $fieldset
     * @param array    $receiverData
     */
    protected function _prepareAddressFields(Fieldset $fieldset, array $receiverData = null)
    {
        $src = $this->getViewFileUrl('Dhl_Versenden::images/dhl_versenden/dhl_logo.png');
        $fieldset->addField(
            'versenden_info_street',
            'separator',
            ['value' => '<img src="' . $src . '" alt="DHL Versenden"/>']
        );

        $fieldset->addField('versenden_info_street_name', 'text', [
            'name'  => "versenden_info[street_name]",
            'label' => __('Street Name'),
            'value' => isset($receiverData['street_name']) ? $receiverData['street_name'] : '',
        ]);

        $fieldset->addField('versenden_info_street_number', 'text', [
            'name'  => "versenden_info[street_number]",
            'label' => __('House number'),
            'value' => isset($receiverData['street_number']) ? $receiverData['street_number'] : '',
        ]);

        $fieldset->addField('versenden_info_address_addition', 'text', [
            'name'  => "versenden_info[address_addition]",
            'label' => __('Address Addition'),
            'value' => isset($receiverData['address_addition']) ? $receiverData['address_addition'] : '',
        ]);
    }
}
