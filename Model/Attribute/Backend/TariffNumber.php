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
 * @package   Dhl\Shipping\Model\Attribute
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class TariffNumber
 * Backend validation class for the tariff number attribute
 */
class TariffNumber extends AbstractBackend
{
    const CODE = 'dhl_tariff_number';

    /**
     * @inheritdoc
     * @param \Magento\Framework\DataObject $object
     */
    public function validate($object)
    {
        $value = $object->getData(self::CODE);
        $frontendLabel = $this->getAttribute()->getData('frontend_label');
        if ($value != '' && !is_numeric($value)) {
            throw new LocalizedException(
                __(
                    'The value of attribute "%1" must be numeric',
                    $frontendLabel
                )
            );
        }
        if (strlen((string)$value) > 11) {
            throw new LocalizedException(
                __(
                    'The value of attribute "%1" must be not be longer than 11 digits',
                    $frontendLabel
                )
            );
        }
        return parent::validate($object);
    }
}
