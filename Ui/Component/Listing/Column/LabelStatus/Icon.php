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
 * @package   Dhl\Shipping\Ui
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Ui\Component\Listing\Column\LabelStatus;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Asset\Repository;
use Dhl\Shipping\Model\Label\LabelStatus;

/**
 * Icon
 *
 * @package  Dhl\Shipping\Ui
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Icon extends Column
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * Icon constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param Repository $assetRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        Repository $assetRepository,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->assetRepo = $assetRepository;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $fieldname = $this->getName();
        $format = '<img src="%s" alt="| %s" title="%s" class="dhl-status-icon"/>';
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item[$fieldname] === null) {
                    $item[$fieldname] = $this->escaper->escapeHtml(__('Not available'));
                } elseif ($item[$fieldname] === LabelStatus::CODE_PROCESSED) {
                    $src = $this->assetRepo->getUrl('Dhl_Shipping::images/dhl_shipping/icon_complete.png');
                    $alt = $this->escaper->escapeHtml(__('DHL Label Status (processed)'));
                    $title = $this->escaper->escapeHtml(__($item[$fieldname]));
                    $item[$fieldname] = sprintf($format, $src, $alt, $title);
                } elseif ($item[$fieldname] === LabelStatus::CODE_FAILED) {
                    $src = $this->assetRepo->getUrl('Dhl_Shipping::images/dhl_shipping/icon_failed.png');
                    $alt = $this->escaper->escapeHtml(__('DHL Label Status (failed)'));
                    $title = $this->escaper->escapeHtml(__($item[$fieldname]));
                    $item[$fieldname] = sprintf($format, $src, $alt, $title);
                } else {
                    $src = $this->assetRepo->getUrl('Dhl_Shipping::images/dhl_shipping/icon_incomplete.png');
                    $alt = $this->escaper->escapeHtml(__('DHL Label Status (pending)'));
                    $title = $this->escaper->escapeHtml(__($item[$fieldname]));
                    $item[$fieldname] = sprintf($format, $src, $alt, $title);
                }
            }
        }
        return $dataSource;
    }
}
