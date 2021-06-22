<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModuleModelFactory;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionProperty;

class ModuleModelFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        // Ensure we start with the default short array notation flag value
        $r = new ReflectionProperty(ModuleModel::class, 'useShortArrayNotation');
        $r->setAccessible(true);
        $r->setValue(false);
    }

    public function testFactoryRaisesExceptionForMissingModuleManagerInContainer(): void
    {
        $factory = new ModuleModelFactory();

        $this->container->has('ModuleManager')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('ModuleManager service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredModuleModel(): void
    {
        $factory       = new ModuleModelFactory();
        $config        = [
            'api-tools-rest' => ['rest configuration' => true],
            'api-tools-rpc'  => ['rpc configuration' => true],
        ];
        $moduleManager = $this->prophesize(ModuleManager::class)->reveal();

        $this->container->has('ModuleManager')->willReturn(true);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->get('ModuleManager')->willReturn($moduleManager);

        $model = $factory($this->container->reveal());

        self::assertInstanceOf(ModuleModel::class, $model);
        //self::assertAttributeSame($moduleManager, 'moduleManager', $model);
        //self::assertAttributeEquals(array_keys($config['api-tools-rest']), 'restConfig', $model);
        //self::assertAttributeEquals(array_keys($config['api-tools-rpc']), 'rpcConfig', $model);
    }

    public function testFactoryCanConfigureShortArrayNotationFlag(): void
    {
        $factory       = new ModuleModelFactory();
        $config        = [
            'api-tools-configuration' => ['enable_short_array' => true],
            'api-tools-rest'          => ['rest configuration' => true],
            'api-tools-rpc'           => ['rpc configuration' => true],
        ];
        $moduleManager = $this->prophesize(ModuleManager::class)->reveal();

        $this->container->has('ModuleManager')->willReturn(true);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->get('ModuleManager')->willReturn($moduleManager);

        $model = $factory($this->container->reveal());
        self::assertInstanceOf(ModuleModel::class, $model);

        $r = new ReflectionProperty(ModuleModel::class, 'useShortArrayNotation');
        $r->setAccessible(true);
        $flag = $r->getValue();
        self::assertTrue($flag, 'useShortArrayNotation flag was not enabled');
    }
}
