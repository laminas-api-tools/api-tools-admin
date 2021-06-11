<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class SourceControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ModuleModel */
    private $model;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->model     = $this->prophesize(ModuleModel::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ModuleModel::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsSourceController(): void
    {
        $factory = new SourceControllerFactory();

        $controller = $factory($this->container->reveal(), SourceController::class);

        self::assertInstanceOf(SourceController::class, $controller);
        //self::assertAttributeSame($this->model, 'moduleModel', $controller);
    }

    public function testLegacyFactoryReturnsSourceController(): void
    {
        $factory = new SourceControllerFactory();
        /** @var ObjectProphecy|AbstractPluginManager $controllers */
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(SourceController::class, $controller);
        //self::assertAttributeSame($this->model, 'moduleModel', $controller);
    }
}
