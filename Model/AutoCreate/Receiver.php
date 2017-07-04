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
 * @category  Dhl
 * @package   Dhl\Shipping\Model\AutoCreate
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\AutoCreate;

use Magento\Framework\DataObject;
use Magento\Sales\Model\Order\Address;

class Receiver extends DataObject
{
    /**
     * @param Address $receiverAddress
     */
    public function importShippingAddress(Address $receiverAddress)
    {
        $this->setRecipientContactPersonName(
            trim($receiverAddress->getFirstname() . ' ' . $receiverAddress->getLastname())
        );
        $this->setRecipientContactPersonFirstName($receiverAddress->getFirstname());
        $this->setRecipientContactPersonLastName($receiverAddress->getLastname());
        $this->setRecipientContactCompanyName($receiverAddress->getCompany());
        $this->setRecipientContactPhoneNumber($receiverAddress->getTelephone());
        $this->setRecipientEmail($receiverAddress->getEmail());
        $this->setRecipientAddressStreet(
            trim($receiverAddress->getStreetLine(1) . ' ' . $receiverAddress->getStreetLine(2))
        );
        $this->setRecipientAddressStreet1($receiverAddress->getStreetLine(1));
        $this->setRecipientAddressStreet2($receiverAddress->getStreetLine(2));
        $this->setRecipientAddressCity($receiverAddress->getCity());
        $this->setRecipientAddressStateOrProvinceCode(
            $receiverAddress->getRegionCode() ?: $receiverAddress->getRegion()
        );
        $this->setRecipientAddressRegionCode($receiverAddress->getRegionCode());
        $this->setRecipientAddressPostalCode($receiverAddress->getPostcode());
        $this->setRecipientAddressCountryCode($receiverAddress->getCountryId());
    }

    public static function fromShippingAddress(Address $receiverAddress)
    {
        $receiver = new self;
        $receiver->importShippingAddress($receiverAddress);
        return $receiver;
    }
}