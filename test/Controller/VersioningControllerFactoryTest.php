<?php

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

class VersioningControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->model = $this->prophesize(ModuleVersioningModelFactory::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ModuleVersioningModelFactory::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsVersioningController()
    {
        $factory = new VersioningControllerFactory();

        $controller = $factory($this->container->reveal(), VersioningController::class);

        $this->assertInstanceOf(VersioningController::class, $controller);
        $this->assertAttributeSame($this->model, 'modelFactory', $controller);
    }

    public function testLegacyFactoryReturnsVersioningController()
    {
        $factory = new VersioningControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(VersioningController::class, $controller);
        $this->assertAttributeSame($this->model, 'modelFactory', $controller);
    }
}
