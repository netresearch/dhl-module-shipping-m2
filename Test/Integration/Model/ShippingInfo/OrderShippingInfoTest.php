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
namespace Dhl\Shipping\Model\Shipping;

use \Dhl\Shipping\Model\ShippingInfo\OrderShippingInfo;
use \Magento\TestFramework\ObjectManager;

/**
 * OrderShippingInfoTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class OrderShippingInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var OrderShippingInfo
     */
    private $shippingInfo;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->shippingInfo = $this->objectManager->create(OrderShippingInfo::class);
    }

    /**
     * Assert value is passed through getter/setter
     *
     * @test
     */
    public function getAddressId()
    {
        $addressId = 12;

        $this->shippingInfo->setAddressId($addressId);
        $this->assertEquals($addressId, $this->shippingInfo->getAddressId());
    }

    /**
     * Assert value is passed through getter/setter
     *
     * @test
     */
    public function getInfo()
    {
        $info = 'info foo';
        $this->shippingInfo->setInfo($info);
        $this->assertEquals($info, $this->shippingInfo->getInfo());
    }
}
