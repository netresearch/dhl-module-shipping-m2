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
 * @author    Max Melzer <max.melzer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ExportDescription
 * Backend validation class for the DHL Export Description attribute
 */
class ExportDescription extends AbstractBackend
{
    const CODE = 'dhl_export_description';
    const MAX_LENGTH = 50;

    /**
     * @inheritdoc
     * @param \Magento\Framework\DataObject $object
     */
    public function validate($object)
    {
        $value = $object->getData(self::CODE);
        $frontendLabel = $this->getAttribute()->getData('frontend_label');
        if (strlen((string)$value) > static::MAX_LENGTH) {
            throw new LocalizedException(
                __(
                    'The value of attribute "%1" must be not be longer than %2 characters',
                    $frontendLabel,
                    static::MAX_LENGTH
                )
            );
        }

        return parent::validate($object);
    }
}
