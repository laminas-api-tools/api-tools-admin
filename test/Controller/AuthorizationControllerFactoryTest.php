<?php

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\AuthorizationModelFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

class AuthorizationControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->authFactory = $this->prophesize(AuthorizationModelFactory::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(AuthorizationModelFactory::class)->willReturn($this->authFactory);
    }

    public function testInvokableFactoryReturnsAuthorizationController()
    {
        $factory = new AuthorizationControllerFactory();

        $controller = $factory($this->container->reveal(), AuthorizationController::class);

        $this->assertInstanceOf(AuthorizationController::class, $controller);
        $this->assertAttributeSame($this->authFactory, 'factory', $controller);
    }

    public function testLegacyFactoryReturnsAuthorizationController()
    {
        $factory = new AuthorizationControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(AuthorizationController::class, $controller);
        $this->assertAttributeSame($this->authFactory, 'factory', $controller);
    }
}
