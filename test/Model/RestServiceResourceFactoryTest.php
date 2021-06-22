<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\Admin\Model\InputFilterModel;
use Laminas\ApiTools\Admin\Model\RestServiceModelFactory;
use Laminas\ApiTools\Admin\Model\RestServiceResource;
use Laminas\ApiTools\Admin\Model\RestServiceResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class RestServiceResourceFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionWhenMissingRestServiceModelFactoryInContainer(): void
    {
        $factory = new RestServiceResourceFactory();

        $this->container->has(RestServiceModelFactory::class)->willReturn(false);

        $this->container->has(\ZF\Apigility\Admin\Model\RestServiceModelFactory::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . RestServiceModelFactory::class . ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingInputFilterModelInContainer(): void
    {
        $factory = new RestServiceResourceFactory();

        $this->container->has(RestServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\InputFilterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . InputFilterModel::class . ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredRestServiceResource(): void
    {
        $factory            = new RestServiceResourceFactory();
        $restFactory        = $this->prophesize(RestServiceModelFactory::class)->reveal();
        $inputFilterModel   = $this->prophesize(InputFilterModel::class)->reveal();
        $documentationModel = $this->prophesize(DocumentationModel::class)->reveal();

        $this->container->has(RestServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(true);

        $this->container->get(RestServiceModelFactory::class)->willReturn($restFactory);
        $this->container->get(InputFilterModel::class)->willReturn($inputFilterModel);
        $this->container->get(DocumentationModel::class)->willReturn($documentationModel);

        $resource = $factory($this->container->reveal());

        self::assertInstanceOf(RestServiceResource::class, $resource);
        //self::assertAttributeSame($restFactory, 'restFactory', $resource);
        //self::assertAttributeSame($inputFilterModel, 'inputFilterModel', $resource);
        //self::assertAttributeSame($documentationModel, 'documentationModel', $resource);
    }
}
