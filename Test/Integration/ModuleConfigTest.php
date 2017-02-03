<?php
namespace Dhl\Versenden;

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
        $this->assertArrayHasKey('Dhl_Versenden', $registrar->getPaths(ComponentRegistrar::MODULE));
    }

    /**
     * @test
     */
    public function bcsLibLoaded()
    {
        $className = \Dhl\Versenden\Bcs\Soap\GVAPI_2_0_de::class;

        try {
            $libObject = $this->objectManager->create($className);
        } catch (\ReflectionException $e) {
            $libObject = null;
        }

        $this->assertInstanceOf($className, $libObject);

        $className = \Dhl\Versenden\Api\Webservice\Adapter\GkAdapter::class;

        try {
            $libObject = $this->objectManager->create($className);
        } catch (\ReflectionException $e) {
            $libObject = null;
        }

        $this->assertInstanceOf($className, $libObject);
    }
}
