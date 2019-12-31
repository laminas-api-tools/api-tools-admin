<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\AuthenticationModel;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

class AuthenticationControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->model = $this->prophesize(AuthenticationModel::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(AuthenticationModel::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsAuthenticationController()
    {
        $factory = new AuthenticationControllerFactory();

        $controller = $factory($this->container->reveal(), AuthenticationController::class);

        $this->assertInstanceOf(AuthenticationController::class, $controller);
        $this->assertAttributeSame($this->model, 'model', $controller);
    }

    public function testLegacyFactoryReturnsAuthenticationController()
    {
        $factory = new AuthenticationControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(AuthenticationController::class, $controller);
        $this->assertAttributeSame($this->model, 'model', $controller);
    }
}
