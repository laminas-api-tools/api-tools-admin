<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ModuleCreationControllerFactoryTest extends TestCase
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

    public function testInvokableFactoryReturnsModuleCreationController(): void
    {
        $factory = new ModuleCreationControllerFactory();

        $controller = $factory($this->container->reveal(), ModuleCreationController::class);

        self::assertInstanceOf(ModuleCreationController::class, $controller);
        //self::assertAttributeSame($this->model, 'moduleModel', $controller);
    }

    public function testLegacyFactoryReturnsModuleCreationController(): void
    {
        $factory = new ModuleCreationControllerFactory();
        /** @var ObjectProphecy|AbstractPluginManager $controllers */
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(ModuleCreationController::class, $controller);
        //self::assertAttributeSame($this->model, 'moduleModel', $controller);
    }
}
