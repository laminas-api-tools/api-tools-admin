<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\ModuleResource;
use Laminas\ApiTools\Admin\Model\ModuleResourceFactory;
use PHPUnit\Framework\TestCase;

class ModuleResourceFactoryTest extends TestCase
{
    public function testFactoryReturnsConfiguredModuleResource()
    {
        $factory = new ModuleResourceFactory();
        $model = $this->prophesize(ModuleModel::class)->reveal();
        $pathSpec = $this->prophesize(ModulePathSpec::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(ModuleModel::class)->willReturn($model);
        $container->get(ModulePathSpec::class)->willReturn($pathSpec);

        $resource = $factory($container->reveal());

        $this->assertInstanceOf(ModuleResource::class, $resource);
        $this->assertAttributeSame($model, 'modules', $resource);
        $this->assertAttributeSame($pathSpec, 'modulePathSpec', $resource);
    }
}
