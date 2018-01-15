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
 * @package   Dhl\Shipping\AutoCreate
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\AutoCreate;

/**
 * Interface LabelGeneratorInterface
 *
 * @package Dhl\Shipping\AutoCreate
 * @author  Paul Siedler <paul.siedler@netresearch.de>
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link    http://www.netresearch.de/
 */
interface LabelGeneratorInterface
{
    /**
     * Creates Labels for the given Shipment through the corresponding carrier, saves corresponding tracks and labels
     * @see \Magento\Shipping\Model\Shipping\LabelGenerator::create()
     *
     * @param \Magento\Sales\Api\Data\ShipmentInterface $shipment
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Pdf_Exception
     */
    public function create(\Magento\Sales\Api\Data\ShipmentInterface $shipment);
}
