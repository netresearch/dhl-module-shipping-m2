<?php

namespace Dhl\Shipping\Model\Shipping\Service;
use Dhl\Shipping\Service\ServiceInterface as LibServiceInterface;

/**
 * Class Service
 *
 * @package \Dhl\Shipping\Model\Shipping\Service
 */
class Service implements ServiceInterface, LibServiceInterface
{
    /**
     * Localized service name.
     *
     * @var string
     */
    private $name;

    /**
     * Indicates whether service is available for selection or not.
     *
     * @var bool
     */
    private $enabled;

    /**
     * Indicates whether service was selected or not.
     * @var bool
     */
    private $selected;

    /**
     * Additional service detail placeholder
     * @var string
     */
    private $placeholder;

    /**
     * Additional service detail maxlength
     * @var int
     */
    private $maxLength;

    /**
     * Additional service detail selection
     * @var string
     */
    private $value;

    /**
     * List of possible service values.
     * @see GenericWithDetails::$value
     * Format:
     *   key => localized value
     * @var string[]
     */
    private $options;

    /**
     * @var LibServiceInterface
     */
    private $apiService;

    /**
     * Service constructor.
     * @param LibServiceInterface $apiService
     * @param $name
     * @param $isEnabled
     * @param $isSelected
     * @param string $placeholder
     * @param int $maxLength
     * @param array $options
     */
    public function __construct(
        LibServiceInterface $apiService,
        $name,
        $isEnabled,
        $isSelected,
        $placeholder = '',
        $maxLength = 100,
        $options = []
    ) {
        $this->apiService = $apiService;
        $this->name = $name;
        $this->enabled = $isEnabled;
        $this->selected = $isSelected;
        $this->placeholder = $placeholder;
        $this->maxLength = $maxLength;
        $this->options = $options;
    }

    public function getCode(): string
    {
        return $this->apiService->getCode();
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function isApplicableToPostalFacility()
    {
        return $this->apiService->isApplicableToPostalFacility();
    }

    public function isApplicableToMerchantSelection()
    {
        return $this->apiService->isApplicableToMerchantSelection();
    }

    public function isApplicableToCustomerSelection()
    {
        return $this->apiService->isApplicableToMerchantSelection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return boolean
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return \string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getApiClass()
    {
        return get_class($this->apiService);
    }
}
