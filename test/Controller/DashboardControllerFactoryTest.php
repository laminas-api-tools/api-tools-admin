<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DashboardControllerFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var Model\AuthenticationModel */
    private $authenticationModel;
    /** @var Model\ContentNegotiationModel */
    private $contentNegotiationModel;
    /** @var Model\DbAdapterModel */
    private $dbAdapterModel;
    /** @var Model\ModuleModel */
    private $moduleModel;
    /** @var Model\RestServiceModelFactory */
    private $restServiceModelFactory;
    /** @var Model\RpcServiceModelFactory */
    private $rpcServiceModelFactory;
    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->authenticationModel     = $this->prophesize(Model\AuthenticationModel::class)->reveal();
        $this->contentNegotiationModel = $this->prophesize(Model\ContentNegotiationModel::class)->reveal();
        $this->dbAdapterModel          = $this->prophesize(Model\DbAdapterModel::class)->reveal();
        $this->moduleModel             = $this->prophesize(Model\ModuleModel::class)->reveal();
        $this->restServiceModelFactory = $this->prophesize(Model\RestServiceModelFactory::class)->reveal();
        $this->rpcServiceModelFactory  = $this->prophesize(Model\RpcServiceModelFactory::class)->reveal();

        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(Model\AuthenticationModel::class)->willReturn($this->authenticationModel);
        $this->container->get(Model\ContentNegotiationModel::class)->willReturn($this->contentNegotiationModel);
        $this->container->get(Model\DbAdapterModel::class)->willReturn($this->dbAdapterModel);
        $this->container->get(Model\ModuleModel::class)->willReturn($this->moduleModel);
        $this->container->get(Model\RestServiceModelFactory::class)->willReturn($this->restServiceModelFactory);
        $this->container->get(Model\RpcServiceModelFactory::class)->willReturn($this->rpcServiceModelFactory);
    }

    public function testInvokableFactoryReturnsDashboardController(): void
    {
        $factory = new DashboardControllerFactory();

        $controller = $factory($this->container->reveal(), DashboardController::class);

        self::assertInstanceOf(DashboardController::class, $controller);
        //self::assertAttributeSame($this->authenticationModel, 'authentication', $controller);
        //self::assertAttributeSame($this->contentNegotiationModel, 'contentNegotiation', $controller);
        //self::assertAttributeSame($this->dbAdapterModel, 'dbAdapters', $controller);
        //self::assertAttributeSame($this->moduleModel, 'modules', $controller);
        //self::assertAttributeSame($this->restServiceModelFactory, 'restServicesFactory', $controller);
        //self::assertAttributeSame($this->rpcServiceModelFactory, 'rpcServicesFactory', $controller);
    }

    public function testLegacyFactoryReturnsDashboardController(): void
    {
        $factory = new DashboardControllerFactory();
        /** @var ObjectProphecy|AbstractPluginManager $controllers */
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        self::assertInstanceOf(DashboardController::class, $controller);
        //self::assertAttributeSame($this->authenticationModel, 'authentication', $controller);
        //self::assertAttributeSame($this->contentNegotiationModel, 'contentNegotiation', $controller);
        //self::assertAttributeSame($this->dbAdapterModel, 'dbAdapters', $controller);
        //self::assertAttributeSame($this->moduleModel, 'modules', $controller);
        //self::assertAttributeSame($this->restServiceModelFactory, 'restServicesFactory', $controller);
        //self::assertAttributeSame($this->rpcServiceModelFactory, 'rpcServicesFactory', $controller);
    }
}
