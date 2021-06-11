<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\AuthenticationModel;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AuthenticationControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|AuthenticationModel */
    private $model;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->model     = $this->prophesize(AuthenticationModel::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(AuthenticationModel::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsAuthenticationController(): void
    {
        $factory = new AuthenticationControllerFactory();

        $controller = $factory($this->container->reveal(), AuthenticationController::class);

        self::assertInstanceOf(AuthenticationController::class, $controller);
        //self::assertAttributeSame($this->model, 'model', $controller);
    }

    public function testLegacyFactoryReturnsAuthenticationController(): void
    {
        $factory = new AuthenticationControllerFactory();
        /** @var ObjectProphecy|AbstractPluginManager $controllers */
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(AuthenticationController::class, $controller);
        //self::assertAttributeSame($this->model, 'model', $controller);
    }
}
