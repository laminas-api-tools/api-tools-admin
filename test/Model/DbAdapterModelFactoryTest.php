<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DbAdapterModel;
use Laminas\ApiTools\Admin\Model\DbAdapterModelFactory;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\ConfigWriter;
use Laminas\Config\Writer\WriterInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class DbAdapterModelFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing()
    {
        $factory = new DbAdapterModelFactory();

        $this->container->has('config')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('config service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsDbAdapterModelComposingConfigResources()
    {
        $factory = new DbAdapterModelFactory();
        $writer  = $this->prophesize(WriterInterface::class)->reveal();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->container->get(ConfigWriter::class)->willReturn($writer);

        $model = $factory($this->container->reveal());

        $this->assertInstanceOf(DbAdapterModel::class, $model);
        $this->assertAttributeInstanceOf(ConfigResource::class, 'globalConfig', $model);
        $this->assertAttributeInstanceOf(ConfigResource::class, 'localConfig', $model);

        $r = new ReflectionProperty($model, 'globalConfig');
        $r->setAccessible(true);
        $globalConfig = $r->getValue($model);

        $this->assertAttributeSame($writer, 'writer', $globalConfig);
        $this->assertAttributeEquals('config/autoload/global.php', 'fileName', $globalConfig);

        $r = new ReflectionProperty($model, 'localConfig');
        $r->setAccessible(true);
        $localConfig = $r->getValue($model);

        $this->assertAttributeSame($writer, 'writer', $localConfig);
        $this->assertAttributeEquals('config/autoload/local.php', 'fileName', $localConfig);
    }
}
