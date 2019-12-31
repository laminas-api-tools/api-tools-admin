<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterModel;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterModelFactory;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\ConfigWriter;
use Laminas\Config\Writer\WriterInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionProperty;

class DoctrineAdapterModelFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing()
    {
        $factory = new DoctrineAdapterModelFactory();

        $this->container->has('config')->willReturn(false);

        $this->setExpectedException(ServiceNotCreatedException::class, 'config service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsDoctrineAdapterModelComposingConfigResources()
    {
        $factory = new DoctrineAdapterModelFactory();
        $writer  = $this->prophesize(WriterInterface::class)->reveal();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->container->get(ConfigWriter::class)->willReturn($writer);

        $model = $factory($this->container->reveal());

        $this->assertInstanceOf(DoctrineAdapterModel::class, $model);
        $this->assertAttributeInstanceOf(ConfigResource::class, 'globalConfig', $model);
        $this->assertAttributeInstanceOf(ConfigResource::class, 'localConfig', $model);

        $r = new ReflectionProperty($model, 'globalConfig');
        $r->setAccessible(true);
        $globalConfig = $r->getValue($model);

        $this->assertAttributeSame($writer, 'writer', $globalConfig);
        $this->assertAttributeEquals('config/autoload/doctrine.global.php', 'fileName', $globalConfig);

        $r = new ReflectionProperty($model, 'localConfig');
        $r->setAccessible(true);
        $localConfig = $r->getValue($model);

        $this->assertAttributeSame($writer, 'writer', $localConfig);
        $this->assertAttributeEquals('config/autoload/doctrine.local.php', 'fileName', $localConfig);
    }
}
