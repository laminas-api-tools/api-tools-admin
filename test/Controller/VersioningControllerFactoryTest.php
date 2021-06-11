<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class VersioningControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ModuleVersioningModelFactory */
    private $model;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->model     = $this->prophesize(ModuleVersioningModelFactory::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ModuleVersioningModelFactory::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsVersioningController(): void
    {
        $factory = new VersioningControllerFactory();

        $controller = $factory($this->container->reveal(), VersioningController::class);

        self::assertInstanceOf(VersioningController::class, $controller);
        //self::assertAttributeSame($this->model, 'modelFactory', $controller);
    }

    public function testLegacyFactoryReturnsVersioningController(): void
    {
        $factory = new VersioningControllerFactory();
        /** @var ObjectProphecy|AbstractPluginManager $controllers */
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(VersioningController::class, $controller);
        //self::assertAttributeSame($this->model, 'modelFactory', $controller);
    }
}
