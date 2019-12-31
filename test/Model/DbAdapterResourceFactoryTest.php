<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DbAdapterModel;
use Laminas\ApiTools\Admin\Model\DbAdapterResource;
use Laminas\ApiTools\Admin\Model\DbAdapterResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;

class DbAdapterResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfDbAdapterModelIsNotInContainer()
    {
        $factory = new DbAdapterResourceFactory();
        $this->container->has(DbAdapterModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\DbAdapterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(DbAdapterModel::class . ' service is not present');

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredDbAdapterResource()
    {
        $factory = new DbAdapterResourceFactory();
        $model = $this->prophesize(DbAdapterModel::class)->reveal();

        $this->container->has(DbAdapterModel::class)->willReturn(true);
        $this->container->get(DbAdapterModel::class)->willReturn($model);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(DbAdapterResource::class, $resource);
        $this->assertAttributeSame($model, 'model', $resource);
    }
}
