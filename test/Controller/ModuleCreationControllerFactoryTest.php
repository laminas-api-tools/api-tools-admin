<?php

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

class ModuleCreationControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->model = $this->prophesize(ModuleModel::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ModuleModel::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsModuleCreationController()
    {
        $factory = new ModuleCreationControllerFactory();

        $controller = $factory($this->container->reveal(), ModuleCreationController::class);

        $this->assertInstanceOf(ModuleCreationController::class, $controller);
        $this->assertAttributeSame($this->model, 'moduleModel', $controller);
    }

    public function testLegacyFactoryReturnsModuleCreationController()
    {
        $factory = new ModuleCreationControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(ModuleCreationController::class, $controller);
        $this->assertAttributeSame($this->model, 'moduleModel', $controller);
    }
}
