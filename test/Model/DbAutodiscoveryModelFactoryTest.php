<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel;
use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModelFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DbAutodiscoveryModelFactoryTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|ServiceLocatorInterface  */
    private $container;

    public function setUp(): void
    {
        $this->container = $this->prophesize(ServiceLocatorInterface::class);
        $this->container->willImplement(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing(): void
    {
        $factory = new DbAutodiscoveryModelFactory();

        $this->container->has('config')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('config service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsDbAutodiscoveryModelComposingConfigAndContainer(): void
    {
        $factory = new DbAutodiscoveryModelFactory();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);

        $model = $factory($this->container->reveal());

        self::assertInstanceOf(DbAutodiscoveryModel::class, $model);
        //self::assertAttributeEquals([], 'config', $model);
        self::assertSame($this->container->reveal(), $model->getServiceLocator());
    }
}
