<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Admin\Model\ContentNegotiationResource;
use Laminas\ApiTools\Admin\Model\ContentNegotiationResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;

class ContentNegotiationResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfContentNegotiationModelIsNotInContainer()
    {
        $factory = new ContentNegotiationResourceFactory();
        $this->container->has(ContentNegotiationModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\ContentNegotiationModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(ContentNegotiationModel::class . ' service is not present');

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredContentNegotiationResource()
    {
        $factory = new ContentNegotiationResourceFactory();
        $model = $this->prophesize(ContentNegotiationModel::class)->reveal();

        $this->container->has(ContentNegotiationModel::class)->willReturn(true);
        $this->container->get(ContentNegotiationModel::class)->willReturn($model);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(ContentNegotiationResource::class, $resource);
        $this->assertAttributeSame($model, 'model', $resource);
    }
}
