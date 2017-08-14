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
 * @package   Dhl\Shipping\Webservice
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Webservice;

use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\AbstractServiceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\CodFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\ParcelAnnouncementFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\InsuranceFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\VisualCheckOfAgeFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\BulkyGoodsFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\ReturnShipmentFactory;
use \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\PrintOnlyIfCodeableFactory;

/**
 * Generic service factory
 *
 * @category Dhl
 * @package  Dhl\Shipping\Webservice
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class RequestServiceFactory extends AbstractServiceFactory
{
    /**
     * @var CodFactory
     */
    private $codFactory;

    /**
     * @var ParcelAnnouncementFactory
     */
    private $parcelAnnouncementFactory;

    /**
     * @var InsuranceFactory
     */
    private $insuranceFactory;

    /**
     * @var VisualCheckOfAgeFactory
     */
    private $visualCheckOfAgeFactory;

    /**
     * @var BulkyGoodsFactory
     */
    private $bulkyGoodsFactory;

    /**
     * @var ReturnShipmentFactory
     */
    private $returnShipmentFactory;

    /**
     * @var PrintOnlyIfCodeableFactory
     */
    private $printOnlyIfCodeableFactory;

    /**
     * RequestServiceFactory constructor.
     * @param CodFactory $codFactory
     * @param ParcelAnnouncementFactory $parcelAnnouncementFactory
     * @param InsuranceFactory $insuranceFactory
     * @param VisualCheckOfAgeFactory $visualCheckOfAgeFactory
     * @param BulkyGoodsFactory $bulkyGoodsFactory
     * @param ReturnShipmentFactory $returnShipmentFactory
     * @param PrintOnlyIfCodeableFactory $printOnlyIfCodeableFactory
     */
    public function __construct(
        CodFactory $codFactory,
        ParcelAnnouncementFactory $parcelAnnouncementFactory,
        InsuranceFactory $insuranceFactory,
        VisualCheckOfAgeFactory $visualCheckOfAgeFactory,
        BulkyGoodsFactory $bulkyGoodsFactory,
        ReturnShipmentFactory $returnShipmentFactory,
        PrintOnlyIfCodeableFactory $printOnlyIfCodeableFactory
    ) {
        $this->codFactory = $codFactory;
        $this->parcelAnnouncementFactory = $parcelAnnouncementFactory;
        $this->insuranceFactory = $insuranceFactory;
        $this->visualCheckOfAgeFactory = $visualCheckOfAgeFactory;
        $this->bulkyGoodsFactory = $bulkyGoodsFactory;
        $this->returnShipmentFactory = $returnShipmentFactory;
        $this->printOnlyIfCodeableFactory = $printOnlyIfCodeableFactory;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $instanceCode
     * @param mixed[] $data
     * @return \Dhl\Shipping\Webservice\RequestType\CreateShipment\ShipmentOrder\Service\ServiceInterface
     */
    public function create($instanceCode, array $data = [])
    {
        switch ($instanceCode) {
            case AbstractServiceFactory::SERVICE_CODE_COD:
                return $this->codFactory->create($data);
            case AbstractServiceFactory::SERVICE_CODE_PARCEL_ANNOUNCEMENT:
                return $this->parcelAnnouncementFactory->create($data);
            case AbstractServiceFactory::SERVICE_CODE_INSURANCE:
                return $this->insuranceFactory->create($data);
            case AbstractServiceFactory::SERVICE_CODE_VISUAL_CHECK_OF_AGE:
                return $this->visualCheckOfAgeFactory->create($data);
            case AbstractServiceFactory::SERVICE_CODE_BULKY_GOODS:
                return $this->bulkyGoodsFactory->create($data);
            case AbstractServiceFactory::SERVICE_CODE_RETURN_SHIPMENT:
                return $this->returnShipmentFactory->create($data);
            case AbstractServiceFactory::SERVICE_CODE_PRINT_ONLY_IF_CODEABLE:
                return $this->printOnlyIfCodeableFactory->create($data);
            default:
                return null;
        }
    }
}
