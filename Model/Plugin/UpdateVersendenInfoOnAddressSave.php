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

use \Dhl\Versenden\Api\VersendenInfoOrderRepositoryInterface;
use \Dhl\Versenden\Bcs\Api\Info\SerializerFactory;
use \Dhl\Versenden\Bcs\Api\Data\InfoInterface;
use \Dhl\Versenden\Bcs\Api\InfoFactory;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Magento\Sales\Controller\Adminhtml\Order\AddressSave;

/**
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class UpdateVersendenInfoOnAddressSave
{
    /**
     * Versenden Info Order Entity Repository
     *
     * @var VersendenInfoOrderRepositoryInterface
     */
    private $versendenInfoOrderRepository;

    /**
     * Versenden Info Entity
     *
     * @var InfoFactory
     */
    private $infoFactory;

    /**
     * Versenden Info Entity Serializer Factory
     *
     * @var SerializerFactory
     */
    private $serializerFactory;

    /**
     * Versenden Info Entity Serializer Factory
     *
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @param VersendenInfoOrderRepositoryInterface $versendenInfoOrderRepository
     * @param InfoFactory                           $infoFactory
     * @param SerializerFactory                     $serializerFactory
     * @param CountryFactory                        $countryFactory
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        VersendenInfoOrderRepositoryInterface $versendenInfoOrderRepository,
        InfoFactory $infoFactory,
        SerializerFactory $serializerFactory,
        CountryFactory $countryFactory
    ) {
        $this->versendenInfoOrderRepository = $versendenInfoOrderRepository;
        $this->infoFactory                  = $infoFactory;
        $this->serializerFactory            = $serializerFactory;
        $this->countryFactory               = $countryFactory;
    }

    /**
     * Will be called, the moment, the shipping address is saved from the backend edit form
     *
     * @param AddressSave $subject
     *
     * @return array|null
     * @throws NoSuchEntityException
     */
    public function beforeExecute(AddressSave $subject)
    {
        $addressId   = $subject->getRequest()->getParam('address_id');
        $requestData = $subject->getRequest()->getPostValue();

        if (isset($requestData['versenden_info'])) {
            try {
                $versendenInfoEntity = $this->versendenInfoOrderRepository->get($addressId);
                $infoEntity          = $this->infoFactory->create();
                /** @var InfoInterface $info */
                $info = $infoEntity::fromJson($versendenInfoEntity->getDhlVersendenInfo());
            } catch (NoSuchEntityException $e) {
                throw new NoSuchEntityException(__('Info entity for sales order shipping address doesn\'t exist'));
            }

            $country = $this->countryFactory->create()->loadByCode($requestData['country_id']);
            // Filter currently not work
            $regionData = $country->getLoadedRegionCollection()->getItems();
            $regionData = $regionData[$requestData['region_id']]->getData();
            $name       = $requestData['firstname'] . ' ' . $requestData['middlename'] . ' ' . $requestData['lastname'];

            $info->getReceiver()->name1           = $name;
            $info->getReceiver()->name2           = $requestData['company'];
            $info->getReceiver()->streetName      = $requestData['versenden_info']['street_name'];
            $info->getReceiver()->streetNumber    = $requestData['versenden_info']['street_number'];
            $info->getReceiver()->addressAddition = $requestData['versenden_info']['address_addition'];
            $info->getReceiver()->zip             = $requestData['postcode'];
            $info->getReceiver()->city            = $requestData['city'];
            $info->getReceiver()->country         = $country->getName();
            $info->getReceiver()->countryISOCode  = $country->getData('iso2_code');
            $info->getReceiver()->state           = $regionData['name'];
            $info->getReceiver()->phone           = $requestData['telephone'];

            $infoJson = $this->serializerFactory->create()->serialize($info);

            $versendenInfoEntity->setDhlVersendenInfo($infoJson);
            $this->versendenInfoOrderRepository->save($versendenInfoEntity);
        }

        return null;
    }
}
