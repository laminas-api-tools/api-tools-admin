<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

class SourceControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->model     = $this->prophesize(ModuleModel::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ModuleModel::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsSourceController()
    {
        $factory = new SourceControllerFactory();

        $controller = $factory($this->container->reveal(), SourceController::class);

        $this->assertInstanceOf(SourceController::class, $controller);
        $this->assertAttributeSame($this->model, 'moduleModel', $controller);
    }

    public function testLegacyFactoryReturnsSourceController()
    {
        $factory     = new SourceControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(SourceController::class, $controller);
        $this->assertAttributeSame($this->model, 'moduleModel', $controller);
    }
}
