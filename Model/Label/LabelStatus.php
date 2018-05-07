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
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Label;

use Dhl\Shipping\Api\Data\LabelStatusInterface;
use Dhl\Shipping\Model\ResourceModel\Label\LabelStatus as LabelStatusResource;
use Magento\Framework\Model\AbstractModel;

/**
 * LabelStatus
 *
 * @category Dhl
 * @package  Dhl\Shipping\Model\Label
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class LabelStatus extends AbstractModel implements LabelStatusInterface
{
    const FIELD_ORDER_ID = 'order_id';
    const FIELD_STATUS_CODE = 'status_code';

    /**
     * Label not yet requested
     */
    const CODE_PENDING = 'Pending';

    /**
     * Labels retrieved, all items shipped
     */
    const CODE_PROCESSED = 'Processed';

    /**
     * Label request failed
     */
    const CODE_FAILED = 'Failed';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(LabelStatusResource::class);

        parent::_construct();
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return (int) $this->getData(self::FIELD_ORDER_ID);
    }

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        $this->setData(self::FIELD_ORDER_ID, (int) $orderId);

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusCode()
    {
        return $this->getData(self::FIELD_STATUS_CODE);
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->setData(self::FIELD_STATUS_CODE, $statusCode);

        return $this;
    }
}
