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
 * PHP version 5
 *
 * @category  Dhl
 * @package   Dhl\Versenden\Bcs\Api\Service
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2016 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Block\Service\Renderer\Type;

/**
 * Renderer
 *
 * @category Dhl
 * @package  Dhl\Versenden\Bcs\Api\Service
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class Renderer
{
    /** @var Generic */
    protected $service;
    /** @var bool */
    protected $readOnly;
    /** @var string */
    protected $selectedYes = 'Yes';
    /** @var string */
    protected $selectedNo  = 'No';


    /**
     * Renderer constructor.
     * @param Generic $service
     * @param bool $readOnly
     */
    public function __construct(Generic $service, $readOnly = false)
    {
        $this->service = $service;
        $this->readOnly = $readOnly;
    }

    /**
     * @param string $selectedYes
     */
    public function setSelectedYes($selectedYes)
    {
        $this->selectedYes = $selectedYes;
    }

    /**
     * @param string $selectedNo
     */
    public function setSelectedNo($selectedNo)
    {
        $this->selectedNo = $selectedNo;
    }

    /**
     * @return string
     */
    public function getSelectorHtml()
    {
        if ($this->readOnly) {
            return '';
        }

        return $this->service->getSelectorHtml();
    }

    /**
     * @return string
     */
    public function getLabelHtml()
    {
        if ($this->readOnly) {
            return $this->service->getName();
        }

        return $this->service->getLabelHtml();
    }

    /**
     * @return string
     */
    public function getValueHtml()
    {
        if ($this->readOnly) {
            $value = $this->selectedNo;
            if ($this->service instanceof Text && $this->service->getValue()) {
                $value = $this->service->getValue();
            } elseif ($this->service->isSelected()) {
                $value = $this->selectedYes;
            }
            return $value;
        }

        return $this->service->getValueHtml();
    }
}
