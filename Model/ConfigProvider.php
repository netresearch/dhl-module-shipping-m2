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
 * @package   Dhl\Shipping
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model;

use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider
 *
 * @package Dhl\Shipping\Model
 */
class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * ConfigProvider constructor.
     *
     * @param Repository $assetRepo
     */
    public function __construct(Repository $assetRepo)
    {
        $this->assetRepo = $assetRepo;
    }

    /**
     * @return string[]
     */
    public function getConfig()
    {
        $config = [
            'dhl_service_block_before' =>  [
                __('Thanks to the flexible recipient services of DHL Preferred Delivery, you decide when and where you want to receive your parcels.'),
                __('Please choose your preferred delivery options.')
            ],
            'dhl_logo_image_url' => $this->assetRepo->getUrl('Dhl_Shipping::images/dhl_logo.png'),
            'dhl_service_block_after' =>  [],
        ];

        return $config;
    }
}
