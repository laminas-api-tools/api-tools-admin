<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

class ConfigControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->configResource = $this->prophesize(ConfigResource::class)->reveal();
        $this->container      = $this->prophesize(ContainerInterface::class);
        $this->container->get(ConfigResource::class)->willReturn($this->configResource);
    }

    public function testInvokableFactoryReturnsConfigControllerComposingConfigResource()
    {
        $factory = new ConfigControllerFactory();

        $controller = $factory($this->container->reveal(), ConfigController::class);

        $this->assertInstanceOf(ConfigController::class, $controller);
        $this->assertAttributeSame($this->configResource, 'config', $controller);
    }

    public function testLegacyFactoryReturnsConfigControllerComposingConfigResource()
    {
        $factory     = new ConfigControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);
        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(ConfigController::class, $controller);
        $this->assertAttributeSame($this->configResource, 'config', $controller);
    }
}
