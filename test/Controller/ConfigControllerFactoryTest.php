<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ConfigControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ConfigResource */
    private $configResource;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->configResource = $this->prophesize(ConfigResource::class)->reveal();
        $this->container      = $this->prophesize(ContainerInterface::class);
        $this->container->get(ConfigResource::class)->willReturn($this->configResource);
    }

    public function testInvokableFactoryReturnsConfigControllerComposingConfigResource(): void
    {
        $factory = new ConfigControllerFactory();

        $controller = $factory($this->container->reveal(), ConfigController::class);

        self::assertInstanceOf(ConfigController::class, $controller);
        //self::assertAttributeSame($this->configResource, 'config', $controller);
    }

    public function testLegacyFactoryReturnsConfigControllerComposingConfigResource(): void
    {
        $factory     = new ConfigControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);
        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(ConfigController::class, $controller);
        //self::assertAttributeSame($this->configResource, 'config', $controller);
    }
}
