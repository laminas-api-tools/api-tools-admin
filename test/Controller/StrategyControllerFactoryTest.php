<?php

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

class StrategyControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class)->reveal();
    }

    public function testInvokableFactoryReturnsStrategyController()
    {
        $factory = new StrategyControllerFactory();

        $controller = $factory($this->container, StrategyController::class);

        $this->assertInstanceOf(StrategyController::class, $controller);
        $this->assertSame($this->container, $controller->getServiceLocator());
    }

    public function testLegacyFactoryReturnsStrategyController()
    {
        $factory = new StrategyControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->willReturn($this->container);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(StrategyController::class, $controller);
        $this->assertSame($this->container, $controller->getServiceLocator());
    }
}
