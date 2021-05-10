<?php

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;

class ModuleVersioningModelFactoryFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function missingDependencies()
    {
        return [
            'all' => [[
                ConfigResourceFactory::class => false,
                ModulePathSpec::class => false,
            ]],
            'ConfigResourceFactory' => [[
                ConfigResourceFactory::class => false,
                ModulePathSpec::class => true,
            ]],
            'ModulePathSpec' => [[
                ConfigResourceFactory::class => true,
                ModulePathSpec::class => false,
            ]],
        ];
    }

    /**
     * @dataProvider missingDependencies
     */
    public function testFactoryRaisesExceptionWhenMissingDependencies($dependencies)
    {
        $factory = new ModuleVersioningModelFactoryFactory();

        foreach ($dependencies as $dependency => $presence) {
            $this->container->has($dependency)->willReturn($presence);
        }

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing one or more dependencies');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredModuleVersioningModelFactory()
    {
        $factory = new ModuleVersioningModelFactoryFactory();
        $configResourceFactory = $this->prophesize(ResourceFactory::class)->reveal();
        $pathSpec = $this->prophesize(ModulePathSpec::class)->reveal();

        $this->container->has(ConfigResourceFactory::class)->willReturn(true);
        $this->container->has(ModulePathSpec::class)->willReturn(true);
        $this->container->get(ConfigResourceFactory::class)->willReturn($configResourceFactory);
        $this->container->get(ModulePathSpec::class)->willReturn($pathSpec);

        $versioningFactory = $factory($this->container->reveal());

        $this->assertInstanceOf(ModuleVersioningModelFactory::class, $versioningFactory);
        $this->assertAttributeSame($configResourceFactory, 'configFactory', $versioningFactory);
        $this->assertAttributeSame($pathSpec, 'moduleUtils', $versioningFactory);
    }
}
