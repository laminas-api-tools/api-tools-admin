<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class VersioningModelFactoryFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    /** @psalm-return array<string, array{0: array<class-string, bool>}> */
    public function missingDependencies(): array
    {
        return [
            'all'                   => [
                [
                    ConfigResourceFactory::class => false,
                    ModulePathSpec::class        => false,
                ],
            ],
            'ConfigResourceFactory' => [
                [
                    ConfigResourceFactory::class => false,
                    ModulePathSpec::class        => true,
                ],
            ],
            'ModulePathSpec'        => [
                [
                    ConfigResourceFactory::class => true,
                    ModulePathSpec::class        => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider missingDependencies
     * @param array<class-string, bool> $dependencies
     */
    public function testFactoryRaisesExceptionWhenMissingDependencies(array $dependencies): void
    {
        $factory = new VersioningModelFactoryFactory();

        foreach ($dependencies as $dependency => $presence) {
            $this->container->has($dependency)->willReturn($presence);
        }

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing one or more dependencies');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredModuleVersioningModelFactory(): void
    {
        $factory               = new VersioningModelFactoryFactory();
        $configResourceFactory = $this->prophesize(ResourceFactory::class)->reveal();
        $pathSpec              = $this->prophesize(ModulePathSpec::class)->reveal();

        $this->container->has(ConfigResourceFactory::class)->willReturn(true);
        $this->container->has(ModulePathSpec::class)->willReturn(true);
        $this->container->get(ConfigResourceFactory::class)->willReturn($configResourceFactory);
        $this->container->get(ModulePathSpec::class)->willReturn($pathSpec);

        $versioningFactory = $factory($this->container->reveal());

        self::assertInstanceOf(VersioningModelFactory::class, $versioningFactory);
        //self::assertAttributeSame($configResourceFactory, 'configFactory', $versioningFactory);
        //self::assertAttributeSame($pathSpec, 'moduleUtils', $versioningFactory);
    }
}
