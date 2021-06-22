<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\MvcAuth\Authentication\DefaultAuthenticationListener;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AuthenticationTypeControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;
    /** @var DefaultAuthenticationListener */
    private $listener;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->listener  = $this->prophesize(DefaultAuthenticationListener::class)->reveal();
        $this->container->get(DefaultAuthenticationListener::class)->willReturn($this->listener);
    }

    public function testInvokableFactoryReturnsAuthenticationTypeController(): void
    {
        $factory = new AuthenticationTypeControllerFactory();

        $controller = $factory($this->container->reveal(), AuthenticationTypeController::class);

        self::assertInstanceOf(AuthenticationTypeController::class, $controller);
        //self::assertAttributeSame($this->listener, 'authListener', $controller);
    }

    public function testLegacyFactoryReturnsAuthenticationTypeController(): void
    {
        $factory = new AuthenticationTypeControllerFactory();
        /** @var ObjectProphecy|AbstractPluginManager $controllers */
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(AuthenticationTypeController::class, $controller);
        //self::assertAttributeSame($this->listener, 'authListener', $controller);
    }
}
