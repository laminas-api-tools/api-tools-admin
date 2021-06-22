<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ModuleConfigControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ResourceFactory */
    private $configResourceFactory;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->configResourceFactory = $this->prophesize(ResourceFactory::class)->reveal();
        $this->container             = $this->prophesize(ContainerInterface::class);
        $this->container->get(ConfigResourceFactory::class)->willReturn($this->configResourceFactory);
    }

    public function testInvokableFactoryReturnsModuleConfigControllerComposingConfigResourceFactory(): void
    {
        $factory = new ModuleConfigControllerFactory();

        $controller = $factory($this->container->reveal(), ModuleConfigController::class);

        self::assertInstanceOf(ModuleConfigController::class, $controller);
        //self::assertAttributeSame($this->configResourceFactory, 'configFactory', $controller);
    }

    public function testLegacyFactoryReturnsModuleConfigControllerComposingConfigResource(): void
    {
        $factory     = new ModuleConfigControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);
        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(ModuleConfigController::class, $controller);
        //self::assertAttributeSame($this->configResourceFactory, 'configFactory', $controller);
    }
}
