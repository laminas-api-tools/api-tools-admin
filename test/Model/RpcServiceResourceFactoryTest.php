<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

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

class RpcServiceResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionWhenMissingRpcServiceModelFactoryInContainer()
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(false);

        $this->container->has(\ZF\Apigility\Admin\Model\RpcServiceModelFactory::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . RpcServiceModelFactory::class. ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingInputFilterModelInContainer()
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\InputFilterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . InputFilterModel::class. ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingControllerManagerInContainer()
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(true);
        $this->container->has('ControllerManager')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ControllerManager dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredRpcServiceResource()
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

        $this->assertInstanceOf(RpcServiceResource::class, $resource);
        $this->assertAttributeSame($rpcFactory, 'rpcFactory', $resource);
        $this->assertAttributeSame($inputFilterModel, 'inputFilterModel', $resource);
        $this->assertAttributeSame($controllerManager, 'controllerManager', $resource);
        $this->assertAttributeSame($documentationModel, 'documentationModel', $resource);
    }
}
