<?php

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

class ModuleConfigControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->configResourceFactory = $this->prophesize(ResourceFactory::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ConfigResourceFactory::class)->willReturn($this->configResourceFactory);
    }

    public function testInvokableFactoryReturnsModuleConfigControllerComposingConfigResourceFactory()
    {
        $factory = new ModuleConfigControllerFactory();

        $controller = $factory($this->container->reveal(), ModuleConfigController::class);

        $this->assertInstanceOf(ModuleConfigController::class, $controller);
        $this->assertAttributeSame($this->configResourceFactory, 'configFactory', $controller);
    }

    public function testLegacyFactoryReturnsModuleConfigControllerComposingConfigResource()
    {
        $factory = new ModuleConfigControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);
        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(ModuleConfigController::class, $controller);
        $this->assertAttributeSame($this->configResourceFactory, 'configFactory', $controller);
    }
}
