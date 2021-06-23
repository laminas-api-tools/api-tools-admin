<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModelFactory;
use Laminas\ApiTools\Configuration\ConfigWriter;
use Laminas\Config\Writer\WriterInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use ReflectionProperty;

class ContentNegotiationModelFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ContainerInterface */
    private $container;
    /** @var WriterInterface */
    private $writer;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->writer    = $this->prophesize(WriterInterface::class)->reveal();
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing(): void
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

    public function testFactoryReturnsConfiguredContentNegotiationModel(): void
    {
        $factory = new ContentNegotiationModelFactory();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->container->get(ConfigWriter::class)->willReturn($this->writer);

        $model = $factory($this->container->reveal());

        self::assertInstanceOf(ContentNegotiationModel::class, $model);
        //self::assertAttributeInstanceOf(ConfigResource::class, 'globalConfig', $model);

        $r = new ReflectionProperty($model, 'globalConfig');
        $r->setAccessible(true);
        $r->getValue($model);

        //self::assertAttributeEquals([], 'config', $config);
        //self::assertAttributeEquals('config/autoload/global.php', 'fileName', $config);
        //self::assertAttributeSame($this->writer, 'writer', $config);
    }
}
