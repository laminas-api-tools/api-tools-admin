<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\Admin\Model\InputFilterModel;
use Laminas\ApiTools\Admin\Model\RpcServiceModelFactory;
use Laminas\ApiTools\Admin\Model\RpcServiceResource;
use Laminas\ApiTools\Admin\Model\RpcServiceResourceFactory;
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class RpcServiceResourceFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionWhenMissingRpcServiceModelFactoryInContainer(): void
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(false);

        $this->container->has(\ZF\Apigility\Admin\Model\RpcServiceModelFactory::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . RpcServiceModelFactory::class . ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingInputFilterModelInContainer(): void
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\InputFilterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . InputFilterModel::class . ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingControllerManagerInContainer(): void
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(true);
        $this->container->has('ControllerManager')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ControllerManager dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredRpcServiceResource(): void
    {
        $factory            = new RpcServiceResourceFactory();
        $rpcFactory         = $this->prophesize(RpcServiceModelFactory::class)->reveal();
        $inputFilterModel   = $this->prophesize(InputFilterModel::class)->reveal();
        $controllerManager  = $this->prophesize(ControllerManager::class)->reveal();
        $documentationModel = $this->prophesize(DocumentationModel::class)->reveal();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(true);
        $this->container->has('ControllerManager')->willReturn(true);

        $this->container->get(RpcServiceModelFactory::class)->willReturn($rpcFactory);
        $this->container->get(InputFilterModel::class)->willReturn($inputFilterModel);
        $this->container->get('ControllerManager')->willReturn($controllerManager);
        $this->container->get(DocumentationModel::class)->willReturn($documentationModel);

        $resource = $factory($this->container->reveal());

        self::assertInstanceOf(RpcServiceResource::class, $resource);
        //self::assertAttributeSame($rpcFactory, 'rpcFactory', $resource);
        //self::assertAttributeSame($inputFilterModel, 'inputFilterModel', $resource);
        //self::assertAttributeSame($controllerManager, 'controllerManager', $resource);
        //self::assertAttributeSame($documentationModel, 'documentationModel', $resource);
    }
}
