<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit_Framework_TestCase as TestCase;

class ConfigControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->configResource = $this->prophesize(ConfigResource::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
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
        $factory = new ConfigControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);
        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(ConfigController::class, $controller);
        $this->assertAttributeSame($this->configResource, 'config', $controller);
    }
}
