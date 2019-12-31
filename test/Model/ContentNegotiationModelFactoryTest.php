<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModelFactory;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\ConfigWriter;
use Laminas\Config\Writer\WriterInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class ContentNegotiationModelFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->writer = $this->prophesize(WriterInterface::class)->reveal();
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing()
    {
        $factory = new ContentNegotiationModelFactory();

        $this->container->has('config')->willReturn(false);
        $this->container->get('config')->shouldNotBeCalled();
        $this->container->get(ConfigWriter::class)->shouldNotBeCalled();
        $this->container->get(\ZF\Configuration\ConfigWriter::class)->shouldNotBeCalled();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('config service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredContentNegotiationModel()
    {
        $factory = new ContentNegotiationModelFactory();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->container->get(ConfigWriter::class)->willReturn($this->writer);

        $model = $factory($this->container->reveal());

        $this->assertInstanceOf(ContentNegotiationModel::class, $model);
        $this->assertAttributeInstanceOf(ConfigResource::class, 'globalConfig', $model);

        $r = new ReflectionProperty($model, 'globalConfig');
        $r->setAccessible(true);
        $config = $r->getValue($model);

        $this->assertAttributeEquals([], 'config', $config);
        $this->assertAttributeEquals('config/autoload/global.php', 'fileName', $config);
        $this->assertAttributeSame($this->writer, 'writer', $config);
    }
}
