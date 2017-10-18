<?php
namespace Dhl\Shipping;

use \Magento\Framework\Component\ComponentRegistrar;
use \Magento\TestFramework\ObjectManager;

class ModuleConfigTest extends \PHPUnit\Framework\TestCase
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
        // generated classes
        $className = \Dhl\Shipping\Bcs\Version::class;
        try {
            /** @var \Dhl\Shipping\Bcs\Version $libObject */
            $libObject = $this->objectManager->create($className, [
                'majorRelease' => '2',
                'minorRelease' => '2',
                'build' => '',
            ]);
        } catch (\ReflectionException $e) {
            $libObject = null;
        }
        $this->assertInstanceOf($className, $libObject);

        // business customer shipping api access
        $className = \Dhl\Shipping\Webservice\Adapter\BcsAdapter::class;
        try {
            $libObject = $this->objectManager->create($className);
        } catch (\ReflectionException $e) {
            $libObject = null;
        }
        $this->assertInstanceOf($className, $libObject);

        // global label api access
        $className = \Dhl\Shipping\Webservice\Adapter\GlAdapter::class;
        try {
            $libObject = $this->objectManager->create($className);
        } catch (\ReflectionException $e) {
            $libObject = null;
        }
        $this->assertInstanceOf($className, $libObject);
    }
}
