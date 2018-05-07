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
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Plugin\Adminhtml\Packaging;

/**
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class PackagingPlugin
{
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * PackagingPlugin constructor.
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Magento\Shipping\Block\Adminhtml\Order\Packaging $subject
     * @param string $result
     * @return string
     */
    public function afterGetConfigDataJson(
        \Magento\Shipping\Block\Adminhtml\Order\Packaging $subject,
        $result
    ) {
        $data = $this->jsonDecoder->decode($result);
        $urlParams = [
            'shipment_id' => $subject->getRequest()->getParam('shipment_id'),
            'order_id' => $subject->getRequest()->getParam('order_id'),
        ];

        $data['itemsGridUrl'] = $this->urlBuilder->getUrl('dhl/order_shipment/getShippingItemsGrid', $urlParams);

        return $this->jsonEncoder->encode($data);
    }
}
