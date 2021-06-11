<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterModel;
use Laminas\ApiTools\Admin\Model\DoctrineAdapterModelFactory;
use Laminas\ApiTools\Configuration\ConfigWriter;
use Laminas\Config\Writer\WriterInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionProperty;

class DoctrineAdapterModelFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing(): void
    {
        $factory = new DoctrineAdapterModelFactory();

        $this->container->has('config')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('config service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsDoctrineAdapterModelComposingConfigResources(): void
    {
        $factory = new DoctrineAdapterModelFactory();
        $writer  = $this->prophesize(WriterInterface::class)->reveal();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->container->get(ConfigWriter::class)->willReturn($writer);

        $model = $factory($this->container->reveal());

        self::assertInstanceOf(DoctrineAdapterModel::class, $model);
        //self::assertAttributeInstanceOf(ConfigResource::class, 'globalConfig', $model);
        //self::assertAttributeInstanceOf(ConfigResource::class, 'localConfig', $model);

        $r = new ReflectionProperty($model, 'globalConfig');
        $r->setAccessible(true);
        $globalConfig = $r->getValue($model);

        //self::assertAttributeSame($writer, 'writer', $globalConfig);
        //self::assertAttributeEquals('config/autoload/doctrine.global.php', 'fileName', $globalConfig);

        $r = new ReflectionProperty($model, 'localConfig');
        $r->setAccessible(true);
        $localConfig = $r->getValue($model);

        //self::assertAttributeSame($writer, 'writer', $localConfig);
        //self::assertAttributeEquals('config/autoload/doctrine.local.php', 'fileName', $localConfig);
    }
}
