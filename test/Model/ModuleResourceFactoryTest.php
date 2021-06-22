<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\ModuleResource;
use Laminas\ApiTools\Admin\Model\ModuleResourceFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ModuleResourceFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testFactoryReturnsConfiguredModuleResource(): void
    {
        $factory   = new ModuleResourceFactory();
        $model     = $this->prophesize(ModuleModel::class)->reveal();
        $pathSpec  = $this->prophesize(ModulePathSpec::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(ModuleModel::class)->willReturn($model);
        $container->get(ModulePathSpec::class)->willReturn($pathSpec);

        $resource = $factory($container->reveal());

        self::assertInstanceOf(ModuleResource::class, $resource);
        //self::assertAttributeSame($model, 'modules', $resource);
        //self::assertAttributeSame($pathSpec, 'modulePathSpec', $resource);
    }
}
