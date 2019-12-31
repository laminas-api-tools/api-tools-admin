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
use Laminas\ApiTools\Admin\Model\RestServiceModelFactory;
use Laminas\ApiTools\Admin\Model\RestServiceResource;
use Laminas\ApiTools\Admin\Model\RestServiceResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit_Framework_TestCase as TestCase;

class RestServiceResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionWhenMissingRestServicModelFactoryInContainer()
    {
        $factory = new RestServiceResourceFactory();

        $this->container->has(RestServiceModelFactory::class)->willReturn(false);

        $this->container->has(\ZF\Apigility\Admin\Model\RestServiceModelFactory::class)->willReturn(false);

        $this->setExpectedException(
            ServiceNotCreatedException::class,
            'missing its ' . RestServiceModelFactory::class. ' dependency'
        );
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingInputFilterModelInContainer()
    {
        $factory = new RestServiceResourceFactory();

        $this->container->has(RestServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(false);
        $this->container->has(\ZF\Apigility\Admin\Model\InputFilterModel::class)->willReturn(false);

        $this->setExpectedException(
            ServiceNotCreatedException::class,
            'missing its ' . InputFilterModel::class. ' dependency'
        );
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredRestServiceResource()
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

        $this->assertInstanceOf(RestServiceResource::class, $resource);
        $this->assertAttributeSame($restFactory, 'restFactory', $resource);
        $this->assertAttributeSame($inputFilterModel, 'inputFilterModel', $resource);
        $this->assertAttributeSame($documentationModel, 'documentationModel', $resource);
    }
}
