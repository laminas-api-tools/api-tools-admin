<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModuleModelFactory;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionProperty;

class ModuleModelFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);

        // Ensure we start with the default short array notation flag value
        $r = new ReflectionProperty(ModuleModel::class, 'useShortArrayNotation');
        $r->setAccessible(true);
        $r->setValue(false);
    }

    public function testFactoryRaisesExceptionForMissingModuleManagerInContainer()
    {
        $factory = new ModuleModelFactory();

        $this->container->has('ModuleManager')->willReturn(false);

        $this->setExpectedException(ServiceNotCreatedException::class, 'ModuleManager service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredModuleModel()
    {
        $factory = new ModuleModelFactory();
        $config  = [
            'api-tools-rest' => ['rest configuration' => true],
            'api-tools-rpc'  => ['rpc configuration' => true],
        ];
        $moduleManager = $this->prophesize(ModuleManager::class)->reveal();

        $this->container->has('ModuleManager')->willReturn(true);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->get('ModuleManager')->willReturn($moduleManager);

        $model = $factory($this->container->reveal());

        $this->assertInstanceOf(ModuleModel::class, $model);
        $this->assertAttributeSame($moduleManager, 'moduleManager', $model);
        $this->assertAttributeEquals(array_keys($config['api-tools-rest']), 'restConfig', $model);
        $this->assertAttributeEquals(array_keys($config['api-tools-rpc']), 'rpcConfig', $model);
    }

    public function testFactoryCanConfigureShortArrayNotationFlag()
    {
        $factory = new ModuleModelFactory();
        $config  = [
            'api-tools-configuration' => ['enable_short_array' => true],
            'api-tools-rest' => ['rest configuration' => true],
            'api-tools-rpc'  => ['rpc configuration' => true],
        ];
        $moduleManager = $this->prophesize(ModuleManager::class)->reveal();

        $this->container->has('ModuleManager')->willReturn(true);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->get('ModuleManager')->willReturn($moduleManager);

        $model = $factory($this->container->reveal());
        $this->assertInstanceOf(ModuleModel::class, $model);

        $r = new ReflectionProperty(ModuleModel::class, 'useShortArrayNotation');
        $r->setAccessible(true);
        $flag = $r->getValue();
        $this->assertTrue($flag, 'useShortArrayNotation flag was not enabled');
    }
}
