<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class StrategyControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class)->reveal();
    }

    public function testInvokableFactoryReturnsStrategyController(): void
    {
        $factory = new StrategyControllerFactory();

        $controller = $factory($this->container, StrategyController::class);

        self::assertInstanceOf(StrategyController::class, $controller);
        self::assertSame($this->container, $controller->getServiceLocator());
    }

    public function testLegacyFactoryReturnsStrategyController(): void
    {
        $factory = new StrategyControllerFactory();
        /** @var ObjectProphecy|AbstractPluginManager $controllers */
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->willReturn($this->container);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(StrategyController::class, $controller);
        self::assertSame($this->container, $controller->getServiceLocator());
    }
}
