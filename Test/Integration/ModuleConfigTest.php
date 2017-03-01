<?php
namespace Dhl\Shipping;

use \Magento\Framework\Component\ComponentRegistrar;
use \Magento\TestFramework\ObjectManager;

class ModuleConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var $objectManager ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = ObjectManager::getInstance();
    }

    /**
     * @test
     */
    public function moduleIsRegistered()
    {
        $registrar = new ComponentRegistrar();
        $this->assertArrayHasKey('Dhl_Shipping', $registrar->getPaths(ComponentRegistrar::MODULE));
    }

    /**
     * @test
     */
    public function bcsLibLoaded()
    {
        $className = \Dhl\Shipping\Bcs\GVAPI_2_0_de::class;

        try {
            $libObject = $this->objectManager->create($className);
        } catch (\ReflectionException $e) {
            $libObject = null;
        }

        $this->assertInstanceOf($className, $libObject);

        $className = \Dhl\Shipping\Webservice\Adapter\GlAdapter::class;

        try {
            $libObject = $this->objectManager->create($className);
        } catch (\ReflectionException $e) {
            $libObject = null;
        }

        $this->assertInstanceOf($className, $libObject);
    }
}
