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
 * @package   Dhl\Shipping\Test\Integration
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Plugin;

use Magento\TestFramework\Interception\PluginList;
use \Magento\TestFramework\ObjectManager;

/**
 * ShippingInformationManagementPluginTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ShippingInformationManagementPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    public function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->objectManager->removeSharedInstance(PluginList::class);
    }

    /**
     * @test
     */
    public function saveAddressInformation()
    {
        $this->markTestIncomplete('Test fails, what is the assertion at all?');

        $shippingInformationMock = $this->getMock(
            \Magento\Checkout\Model\ShippingInformation::class,
            ['getShippingAddress'],
            [],
            '',
            false
        );

        $address = $this->objectManager->create(\Magento\Quote\Model\Quote\Address::class);
        $shippingInformationMock
            ->expects($this->once())
            ->method('getShippingAddress')
            ->will($this->returnValue($address));

        /** @var \Magento\Checkout\Model\ShippingInformationManagement $model */
        $model = $this->objectManager->create(\Magento\Checkout\Model\ShippingInformationManagement::class);

        $model->saveAddressInformation(12, $shippingInformationMock);
    }
}
