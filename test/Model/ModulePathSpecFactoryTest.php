<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\ModulePathSpecFactory;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;

class ModulePathSpecFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfModuleUtilsServiceIsMissing()
    {
        $factory = new ModulePathSpecFactory();

        $this->container->has(ModuleUtils::class)->willReturn(false);

        $this->container->has(\ZF\Configuration\ModuleUtils::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(ModuleUtils::class . ' service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionIfConfiguredModulePathIsNotADirectory()
    {
        $factory = new ModulePathSpecFactory();
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

    public function testFactoryReturnsConfiguredModulePathSpec()
    {
        $factory = new ModulePathSpecFactory();
        $moduleUtils = $this->prophesize(ModuleUtils::class)->reveal();

        $this->container->has(ModuleUtils::class)->willReturn(true);
        $this->container->get(ModuleUtils::class)->willReturn($moduleUtils);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'api-tools-admin' => [
                'module_path' => realpath(__DIR__),
                'path_spec' => 'psr-4',
            ],
        ]);

        $pathSpec = $factory($this->container->reveal());

        $this->assertInstanceOf(ModulePathSpec::class, $pathSpec);
        $this->assertAttributeSame($moduleUtils, 'modules', $pathSpec);
        $this->assertAttributeEquals(realpath(__DIR__), 'applicationPath', $pathSpec);
        $this->assertAttributeEquals('%modulePath%/src', 'moduleSourcePathSpec', $pathSpec);
    }
}
