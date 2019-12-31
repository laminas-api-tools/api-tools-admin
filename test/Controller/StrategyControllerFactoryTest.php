<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit_Framework_TestCase as TestCase;

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
