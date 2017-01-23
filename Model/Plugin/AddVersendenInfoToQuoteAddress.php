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
 * PHP version 7
 *
 * @category  Dhl
 * @package   Dhl\Versenden
 * @author    Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Model\Plugin;

use \Dhl\Versenden\Api\VersendenInfoQuoteRepositoryInterface;
use \Dhl\Versenden\Model\VersendenInfoQuoteFactory;
use \Magento\Quote\Api\CartRepositoryInterface;

/**
 * Config
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Benjamin Heuer <benjamin.heuer@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class AddVersendenInfoToQuoteAddress
{
    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * Versenden Info Quote Entity
     *
     * @var VersendenInfoQuoteFactory
     */
    private $versendenInfoQuoteFactory;

    /**
     * Versenden Info Quote Entity Repository
     *
     * @var VersendenInfoQuoteRepositoryInterface
     */
    private $versendenInfoQuoteRepository;

    /**
     * @param CartRepositoryInterface               $quoteRepository
     * @param VersendenInfoQuoteFactory             $versendenInfoQuoteFactory
     * @param VersendenInfoQuoteRepositoryInterface $versendenInfoQuoteRepository
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        VersendenInfoQuoteFactory $versendenInfoQuoteFactory,
        VersendenInfoQuoteRepositoryInterface $versendenInfoQuoteRepository
    )
    {
        $this->quoteRepository              = $quoteRepository;
        $this->versendenInfoQuoteFactory    = $versendenInfoQuoteFactory;
        $this->versendenInfoQuoteRepository = $versendenInfoQuoteRepository;
    }

    /**
     * Will be called, the moment, the shipping address is saved
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement   $subject
     * @param                                                         $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     *
     * @return array|null
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    )
    {
        $versendenInfo = $addressInformation->getShippingAddress()->getExtensionAttributes()->getDhlVersendenInfo();
        if ($versendenInfo) {
            $quoteAddressId = $this->quoteRepository->getActive($cartId)->getShippingAddress()->getId();

            // save/override versenden info into extension table
            $versendenInfo = $this->versendenInfoQuoteFactory->create()
                                                             ->setDhlVersendenInfo($versendenInfo)
                                                             ->setQuoteAddressId($quoteAddressId);
            $this->versendenInfoQuoteRepository->save($versendenInfo);
        }

        return null;
    }
}
