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
 * @package   Dhl\Shipping\Block
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Block\Adminhtml\Order\Shipment;

use Dhl\Shipping\Api\Data\Service\ServiceInputInterface;
use Dhl\Shipping\Api\Data\ServiceInterface;
use Dhl\Shipping\Model\ResourceModel\Order\Address\ServiceSelectionCollection;
use Dhl\Shipping\Model\ResourceModel\ServiceSelectionRepository;
use Dhl\Shipping\Model\Service\PackagingServiceProvider;
use Dhl\Shipping\Model\Service\ServiceCollection;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Shipping\Block\Adminhtml\Order\Packaging as MagentoPackaging;
use Magento\Shipping\Model\Carrier\Source\GenericInterface;
use Magento\Shipping\Model\CarrierFactory;

/**
 * Services
 *
 * @package Dhl\Shipping\Block
 * @author  Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.netresearch.de/
 */
class Services extends MagentoPackaging
{
    /**
     * @var PackagingServiceProvider
     */
    private $serviceProvider;

    /**
     * @var ServiceSelectionRepository
     */
    private $serviceSelectionRepository;

    /**
     * Services constructor.
     *
     * @param Context                    $context
     * @param EncoderInterface           $jsonEncoder
     * @param GenericInterface           $sourceSizeModel
     * @param Registry                   $coreRegistry
     * @param CarrierFactory             $carrierFactory
     * @param PackagingServiceProvider   $serviceProvider
     * @param ServiceSelectionRepository $serviceSelectionRepository
     * @param array                      $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        GenericInterface $sourceSizeModel,
        Registry $coreRegistry,
        CarrierFactory $carrierFactory,
        PackagingServiceProvider $serviceProvider,
        ServiceSelectionRepository $serviceSelectionRepository,
        array $data = []
    ) {
        $this->serviceProvider = $serviceProvider;
        $this->serviceSelectionRepository = $serviceSelectionRepository;

        parent::__construct($context, $jsonEncoder, $sourceSizeModel, $coreRegistry, $carrierFactory, $data);
    }

    /**
     * @return ServiceCollection
     */
    public function getServices()
    {
        $shipment = $this->getShipment();

        return $this->serviceProvider->getServices($shipment);
    }

    /**
     * @return ServiceSelectionCollection
     * @throws NoSuchEntityException
     */
    public function getServiceSelection()
    {
        $shipment = $this->getShipment();

        return $this->serviceSelectionRepository->getByOrderAddressId($shipment->getShippingAddressId());
    }

    /**
     * @param ServiceInterface $service
     *
     * @return string
     */
    public function getServiceHtml($service)
    {
        $html = '';

        /** @var ServiceInputInterface $input */
        foreach ($service->getInputs() as $input) {
            if (in_array($input->getInputType(), ['date', 'time'])) {
                $inputType = 'checkboxset';
            } else {
                $inputType = $input->getInputType();
            }
            /** Set block template according to service input type */
            $template = sprintf(
                'Dhl_Shipping::order/packaging/popup/service/%s.phtml',
                $inputType
            );

            /** @var Template $serviceBlock */
            $serviceBlock = $this->getChildBlock('dhl_shipment_packaging_service');
            $serviceBlock->setTemplate($template);
            $serviceBlock->setData('service', $service);
            $serviceBlock->setData('input', $input);
            $html .= $serviceBlock->toHtml();
        }

        return $html;
    }
}
