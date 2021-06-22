<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\RpcServiceModelFactory;
use Laminas\ApiTools\Admin\Model\RpcServiceModelFactoryFactory;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;

class RpcServiceModelFactoryFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /** @psalm-return array<string, array{0: array<string, bool>}> */
    public function missingDependencies()
    {
        return [
            'all'                   => [
                [
                    ModulePathSpec::class        => false,
                    ConfigResourceFactory::class => false,
                    ModuleModel::class           => false,
                    'SharedEventManager'         => false,
                ],
            ],
            'ModulePathSpec'        => [
                [
                    ModulePathSpec::class        => false,
                    ConfigResourceFactory::class => true,
                    ModuleModel::class           => true,
                    'SharedEventManager'         => true,
                ],
            ],
            'ConfigResourceFactory' => [
                [
                    ModulePathSpec::class        => true,
                    ConfigResourceFactory::class => false,
                    ModuleModel::class           => true,
                    'SharedEventManager'         => true,
                ],
            ],
            'ModuleModel'           => [
                [
                    ModulePathSpec::class        => true,
                    ConfigResourceFactory::class => true,
                    ModuleModel::class           => false,
                    'SharedEventManager'         => true,
                ],
            ],
            'SharedEventManager'    => [
                [
                    ModulePathSpec::class        => true,
                    ConfigResourceFactory::class => true,
                    ModuleModel::class           => true,
                    'SharedEventManager'         => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider missingDependencies
     */
    public function testFactoryRaisesExceptionIfDependenciesAreMissing(array $dependencies)
    {
        $factory = new RpcServiceModelFactoryFactory();

        foreach ($dependencies as $dependency => $presence) {
            $this->container->has($dependency)->willReturn($presence);
        }

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing one or more dependencies');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredRpcServiceModelFactory()
    {
        $factory               = new RpcServiceModelFactoryFactory();
        $pathSpec              = $this->prophesize(ModulePathSpec::class)->reveal();
        $configResourceFactory = $this->prophesize(ResourceFactory::class)->reveal();
        $sharedEvents          = $this->prophesize(SharedEventManagerInterface::class)->reveal();
        $moduleModel           = $this->prophesize(ModuleModel::class)->reveal();

        $this->container->has(ModulePathSpec::class)->willReturn(true);
        $this->container->has(ConfigResourceFactory::class)->willReturn(true);
        $this->container->has(ModuleModel::class)->willReturn(true);
        $this->container->has('SharedEventManager')->willReturn(true);

        $this->container->get(ModulePathSpec::class)->willReturn($pathSpec);
        $this->container->get(ConfigResourceFactory::class)->willReturn($configResourceFactory);
        $this->container->get('SharedEventManager')->willReturn($sharedEvents);
        $this->container->get(ModuleModel::class)->willReturn($moduleModel);

        $rpcFactory = $factory($this->container->reveal());

        $this->assertInstanceOf(RpcServiceModelFactory::class, $rpcFactory);
        $this->assertAttributeSame($pathSpec, 'modules', $rpcFactory);
        $this->assertAttributeSame($configResourceFactory, 'configFactory', $rpcFactory);
        $this->assertAttributeSame($sharedEvents, 'sharedEventManager', $rpcFactory);
        $this->assertAttributeSame($moduleModel, 'moduleModel', $rpcFactory);
    }
}
