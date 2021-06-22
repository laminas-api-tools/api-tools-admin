<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\AuthorizationModelFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AuthorizationControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var AuthorizationModelFactory */
    private $authFactory;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->authFactory = $this->prophesize(AuthorizationModelFactory::class)->reveal();
        $this->container   = $this->prophesize(ContainerInterface::class);
        $this->container->get(AuthorizationModelFactory::class)->willReturn($this->authFactory);
    }

    public function testInvokableFactoryReturnsAuthorizationController(): void
    {
        $factory = new AuthorizationControllerFactory();

        $controller = $factory($this->container->reveal(), AuthorizationController::class);

        self::assertInstanceOf(AuthorizationController::class, $controller);
        //self::assertAttributeSame($this->authFactory, 'factory', $controller);
    }

    public function testLegacyFactoryReturnsAuthorizationController(): void
    {
        $factory = new AuthorizationControllerFactory();
        /** @var ObjectProphecy|AbstractPluginManager $controllers */
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(AuthorizationController::class, $controller);
        //self::assertAttributeSame($this->authFactory, 'factory', $controller);
    }
}
