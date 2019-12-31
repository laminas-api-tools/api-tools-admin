<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterModel;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterResource;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterResourceFactory;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_TestCase as TestCase;

class DoctrineAdapterResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ServiceLocatorInterface::class);
        $this->container->willImplement(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfDoctrineAdapterModelIsNotInContainer()
    {
        $factory = new DoctrineAdapterResourceFactory();
        $this->container->has(DoctrineAdapterModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\DoctrineAdapterModel::class)->willReturn(false);

        $this->setExpectedException(
            ServiceNotCreatedException::class,
            DoctrineAdapterModel::class . ' service is not present'
        );

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredDoctrineAdapterResource()
    {
        $factory = new DoctrineAdapterResourceFactory();
        $model = $this->prophesize(DoctrineAdapterModel::class)->reveal();
        $modules = $this->prophesize(ModuleManager::class);

        $this->container->has(DoctrineAdapterModel::class)->willReturn(true);
        $this->container->get(DoctrineAdapterModel::class)->willReturn($model);

        $this->container->get('ModuleManager')->will([$modules, 'reveal']);
        $modules->getLoadedModules(false)->willReturn([
            'FooConf',
            'Version',
        ]);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(DoctrineAdapterResource::class, $resource);
        $this->assertAttributeSame($model, 'model', $resource);
        $this->assertAttributeEquals([
            'FooConf',
            'Version',
        ], 'loadedModules', $resource);
        $this->assertAttributeSame($this->container->reveal(), 'serviceLocator', $resource);
    }
}
