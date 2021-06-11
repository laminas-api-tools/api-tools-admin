<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\ModulePathSpecFactory;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

use function realpath;

class ModulePathSpecFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfModuleUtilsServiceIsMissing(): void
    {
        $factory = new ModulePathSpecFactory();

        $this->container->has(ModuleUtils::class)->willReturn(false);

        // phpcs:ignore WebimpressCodingStandard.Formatting.StringClassReference.Found
        $this->container->has('ZF\Configuration\ModuleUtils')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(ModuleUtils::class . ' service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionIfConfiguredModulePathIsNotADirectory(): void
    {
        $factory     = new ModulePathSpecFactory();
        $moduleUtils = $this->prophesize(ModuleUtils::class)->reveal();

        $this->container->has(ModuleUtils::class)->willReturn(true);
        $this->container->get(ModuleUtils::class)->willReturn($moduleUtils);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'api-tools-admin' => [
                'module_path' => __FILE__,
            ],
        ]);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Invalid module path');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredModulePathSpec(): void
    {
        $factory     = new ModulePathSpecFactory();
        $moduleUtils = $this->prophesize(ModuleUtils::class)->reveal();

        $this->container->has(ModuleUtils::class)->willReturn(true);
        $this->container->get(ModuleUtils::class)->willReturn($moduleUtils);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'api-tools-admin' => [
                'module_path' => realpath(__DIR__),
                'path_spec'   => 'psr-4',
            ],
        ]);

        $pathSpec = $factory($this->container->reveal());

        self::assertInstanceOf(ModulePathSpec::class, $pathSpec);
        //self::assertAttributeSame($moduleUtils, 'modules', $pathSpec);
        //self::assertAttributeEquals(realpath(__DIR__), 'applicationPath', $pathSpec);
        //self::assertAttributeEquals('%modulePath%/src', 'moduleSourcePathSpec', $pathSpec);
    }
}
