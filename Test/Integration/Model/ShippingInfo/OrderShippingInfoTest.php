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

use Dhl\Shipping\Model\ShippingInfo\OrderShippingInfo;
use \Magento\TestFramework\ObjectManager;

/**
 * ConfigTest
 *
 * @category Dhl
 * @package  Dhl\Shipping\Test\Integration
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class OrderShippingInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    /** @var  OrderShippingInfo */
    private $model;



    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
        $this->model = $this->objectManager->create(OrderShippingInfo::class);
    }

    /**
     * @test
     */
    public function getAddressById()
    {
        $this->model->setAddressId(12);
        $this->assertEquals(12, $this->model->getAddressId(12));
    }

    /**
     * @test
     */
    public function getInfo()
    {
        $this->model->setInfo('fooInfo');
        $this->assertEquals('fooInfo', $this->model->getInfo());
    }

}
