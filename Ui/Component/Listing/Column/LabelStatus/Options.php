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

namespace Dhl\Shipping\Ui\Component\Listing\Column\LabelStatus;

use Dhl\Shipping\Model\Label\LabelStatus;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;

/**
 * Options
 *
 * @package  Dhl\Shipping\Ui
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Options implements OptionSourceInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Options constructor.
     *
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => LabelStatus::CODE_PENDING,
                'label' => $this->escaper->escapeHtml(__('Pending'))
            ],
            [
                'value' => LabelStatus::CODE_PROCESSED,
                'label' => $this->escaper->escapeHtml(__('Processed'))
            ],
            [
                'value' => LabelStatus::CODE_FAILED,
                'label' => $this->escaper->escapeHtml(__('Failed'))
            ]
        ];
    }
}
