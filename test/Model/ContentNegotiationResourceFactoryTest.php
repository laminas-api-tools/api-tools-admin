<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Admin\Model\ContentNegotiationResource;
use Laminas\ApiTools\Admin\Model\ContentNegotiationResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ContentNegotiationResourceFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfContentNegotiationModelIsNotInContainer(): void
    {
        $factory = new ContentNegotiationResourceFactory();
        $this->container->has(ContentNegotiationModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\ContentNegotiationModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(ContentNegotiationModel::class . ' service is not present');

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredContentNegotiationResource(): void
    {
        $factory = new ContentNegotiationResourceFactory();
        $model   = $this->prophesize(ContentNegotiationModel::class)->reveal();

        $this->container->has(ContentNegotiationModel::class)->willReturn(true);
        $this->container->get(ContentNegotiationModel::class)->willReturn($model);

        $resource = $factory($this->container->reveal());

        self::assertInstanceOf(ContentNegotiationResource::class, $resource);
        //self::assertAttributeSame($model, 'model', $resource);
    }
}
