<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterModel;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterResource;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterResourceFactory;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DoctrineAdapterResourceFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ServiceLocatorInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ServiceLocatorInterface::class);
        $this->container->willImplement(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfDoctrineAdapterModelIsNotInContainer(): void
    {
        $factory = new DoctrineAdapterResourceFactory();
        $this->container->has(DoctrineAdapterModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\DoctrineAdapterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(DoctrineAdapterModel::class . ' service is not present');

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredDoctrineAdapterResource(): void
    {
        $factory = new DoctrineAdapterResourceFactory();
        $model   = $this->prophesize(DoctrineAdapterModel::class)->reveal();
        $modules = $this->prophesize(ModuleManager::class);

        $this->container->has(DoctrineAdapterModel::class)->willReturn(true);
        $this->container->get(DoctrineAdapterModel::class)->willReturn($model);

        $this->container->get('ModuleManager')->will([$modules, 'reveal']);
        $modules->getLoadedModules(false)->willReturn([
            'FooConf',
            'Version',
        ]);

        $resource = $factory($this->container->reveal());

        self::assertInstanceOf(DoctrineAdapterResource::class, $resource);
        //self::assertAttributeSame($model, 'model', $resource);
        /*self::assertAttributeEquals([
            'FooConf',
            'Version',
        ], 'loadedModules', $resource);*/
        //self::assertAttributeSame($this->container->reveal(), 'serviceLocator', $resource);
    }
}
