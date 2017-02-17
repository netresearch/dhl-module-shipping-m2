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
namespace Dhl\Versenden\Model\ShippingInfo;

use \Dhl\Versenden\Api\Data\ShippingInfoInterface;
use \Dhl\Versenden\Api\ShippingInfoRepositoryInterface;
use Dhl\Versenden\Model\ResourceModel\ShippingInfo\OrderShippingInfo as ShippingInfoResource;
use \Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository class for @see ShippingInfoInterface
 */
class OrderShippingInfoRepository extends AbstractShippingInfoRepository implements ShippingInfoRepositoryInterface
{
    /**
     * @var OrderShippingInfoFactory
     */
    private $shippingInfoFactory;

    /**
     * OrderShippingInfoRepository constructor.
     * @param OrderShippingInfoFactory $shippingInfoFactory
     * @param ShippingInfoResource $shippingInfoResource
     */
    public function __construct(
        OrderShippingInfoFactory $shippingInfoFactory,
        ShippingInfoResource $shippingInfoResource
    ) {
        $this->shippingInfoFactory = $shippingInfoFactory;
        parent::__construct($shippingInfoResource);
    }

    /**
     * Retrieve DHL Shipping Info by PK.
     *
     * @param int $addressId Order Address ID
     * @return ShippingInfoInterface
     * @throws NoSuchEntityException
     */
    public function getById($addressId)
    {
        $shippingInfo = $this->shippingInfoFactory->create();
        $this->shippingInfoResource->load($shippingInfo, $addressId);

        if (!$shippingInfo->getId()) {
            throw new NoSuchEntityException(__('Shipment with id "%1" does not exist.', $addressId));
        }

        return $shippingInfo;
    }
}
