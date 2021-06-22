<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DbAdapterModel;
use Laminas\ApiTools\Admin\Model\DbAdapterResource;
use Laminas\ApiTools\Admin\Model\DbAdapterResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DbAdapterResourceFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfDbAdapterModelIsNotInContainer(): void
    {
        $factory = new DbAdapterResourceFactory();
        $this->container->has(DbAdapterModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\DbAdapterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(DbAdapterModel::class . ' service is not present');

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredDbAdapterResource(): void
    {
        $factory = new DbAdapterResourceFactory();
        $model   = $this->prophesize(DbAdapterModel::class)->reveal();

        $this->container->has(DbAdapterModel::class)->willReturn(true);
        $this->container->get(DbAdapterModel::class)->willReturn($model);

        $resource = $factory($this->container->reveal());

        self::assertInstanceOf(DbAdapterResource::class, $resource);
        //self::assertAttributeSame($model, 'model', $resource);
    }
}
