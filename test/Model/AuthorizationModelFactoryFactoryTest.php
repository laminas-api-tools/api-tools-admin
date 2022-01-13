<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\AuthorizationModelFactory;
use Laminas\ApiTools\Admin\Model\AuthorizationModelFactoryFactory;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class AuthorizationModelFactoryFactoryTest extends TestCase
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
            'all'                                    => [
                [
                    ModulePathSpec::class        => false,
                    ConfigResourceFactory::class => false,
                    ModuleModel::class           => false,
                ],
            ],
            'ModulePathSpec'                         => [
                [
                    ModulePathSpec::class        => false,
                    ConfigResourceFactory::class => true,
                    ModuleModel::class           => true,
                ],
            ],
            'ConfigResourceFactory'                  => [
                [
                    ModulePathSpec::class        => true,
                    ConfigResourceFactory::class => false,
                    ModuleModel::class           => true,
                ],
            ],
            'ModuleModel'                            => [
                [
                    ModulePathSpec::class        => true,
                    ConfigResourceFactory::class => true,
                    ModuleModel::class           => false,
                ],
            ],
            'ModulePathSpec + ConfigResourceFactory' => [
                [
                    ModulePathSpec::class        => false,
                    ConfigResourceFactory::class => false,
                    ModuleModel::class           => true,
                ],
            ],
            'ModulePathSpec + ModuleModel'           => [
                [
                    ModulePathSpec::class        => false,
                    ConfigResourceFactory::class => true,
                    ModuleModel::class           => false,
                ],
            ],
            'ConfigResourceFactory + ModuleModel'    => [
                [
                    ModulePathSpec::class        => true,
                    ConfigResourceFactory::class => false,
                    ModuleModel::class           => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider missingDependencies
     * @param array<class-string, bool> $dependencies
     */
    public function testFactoryRaisesExceptionIfAnyDependenciesAreMissing(array $dependencies): void
    {
        $factory = new AuthorizationModelFactoryFactory();

        foreach ($dependencies as $dependency => $presence) {
            $this->container->has($dependency)->willReturn($presence);
        }

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing one or more dependencies');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsAuthorizationModelFactory(): void
    {
        $factory               = new AuthorizationModelFactoryFactory();
        $modulePathSpec        = $this->prophesize(ModulePathSpec::class);
        $configResourceFactory = $this->prophesize(ResourceFactory::class);
        $moduleModel           = $this->prophesize(ModuleModel::class);

        $this->container->has(ModulePathSpec::class)->willReturn(true);
        $this->container->get(ModulePathSpec::class)->will([$modulePathSpec, 'reveal']);
        $this->container->has(ConfigResourceFactory::class)->willReturn(true);
        $this->container->get(ConfigResourceFactory::class)->will([$configResourceFactory, 'reveal']);
        $this->container->has(ModuleModel::class)->willReturn(true);
        $this->container->get(ModuleModel::class)->will([$moduleModel, 'reveal']);

        $modelFactory = $factory($this->container->reveal());

        self::assertInstanceOf(AuthorizationModelFactory::class, $modelFactory);
        //self::assertAttributeSame($modulePathSpec->reveal(), 'modules', $modelFactory);
        //self::assertAttributeSame($configResourceFactory->reveal(), 'configFactory', $modelFactory);
        //self::assertAttributeSame($moduleModel->reveal(), 'moduleModel', $modelFactory);
    }
}
