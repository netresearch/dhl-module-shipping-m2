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
namespace Dhl\Shipping\Model\ShippingInfo;

use \Dhl\Shipping\Model\ResourceModel\ShippingInfo\AbstractShippingInfo as ShippingInfoResource;
use \Dhl\Shipping\Webservice\ShippingInfo\Info;
use \Magento\Framework\Exception\CouldNotDeleteException;
use \Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

abstract class AbstractShippingInfoRepository implements ShippingInfoRepositoryInterface
{
    /**
     * @var ShippingInfoResource
     */
    protected $shippingInfoResource;

    /**
     * AbstractShippingInfoRepository constructor.
     * @param ShippingInfoResource $shippingInfoResource
     */
    public function __construct(ShippingInfoResource $shippingInfoResource)
    {
        $this->shippingInfoResource = $shippingInfoResource;
    }

    /**
     * Save DHL Shipping Info. PK equals Address ID.
     *
     * @param ShippingInfoInterface $shippingInfo
     * @return ShippingInfoInterface
     * @throws CouldNotSaveException
     */
    public function save(ShippingInfoInterface $shippingInfo)
    {
        try {
            $this->shippingInfoResource->save($shippingInfo);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $shippingInfo;
    }

    /**
     * Retrieve DHL Shipping Info by Address id.
     *
     * @param int $addressId Order Address ID or Quote Address ID
     * @return ShippingInfoInterface
     * @throws NoSuchEntityException
     */
    abstract public function getById($addressId);

    /**
     * Delete DHL Shipping Info
     *
     * @param ShippingInfoInterface $shippingInfo
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ShippingInfoInterface $shippingInfo)
    {
        try {
            $this->shippingInfoResource->delete($shippingInfo);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * Delete DHL Shipping Info using PK
     *
     * @param int $addressId
     * @return bool
     */
    public function deleteById($addressId)
    {
        $entity = $this->getById($addressId);
        return $this->delete($entity);
    }

    /**
     * @param $addressId
     * @return \Dhl\Shipping\Webservice\ShippingInfo\AbstractInfo|null
     */
    public function getInfoData($addressId)
    {
        try {
            $shippingInfo = $this->getById($addressId);
            $jsonInfo = $shippingInfo->getInfo();
            $infoData = $jsonInfo ? Info::fromJson($shippingInfo->getInfo()) : null;
        } catch (NoSuchEntityException $e) {
            $infoData = null;
        }

        return $infoData;
    }
}
