<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel;
use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModelFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit\Framework\TestCase;

class DbAutodiscoveryModelFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ServiceLocatorInterface::class);
        $this->container->willImplement(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing()
    {
        $factory = new DbAutodiscoveryModelFactory();

        $this->container->has('config')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('config service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsDbAutodiscoveryModelComposingConfigAndContainer()
    {
        $factory = new DbAutodiscoveryModelFactory();
        $writer  = $this->prophesize(WriterInterface::class)->reveal();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);

        $model = $factory($this->container->reveal());

        $this->assertInstanceOf(DbAutodiscoveryModel::class, $model);
        $this->assertAttributeEquals([], 'config', $model);
        $this->assertSame($this->container->reveal(), $model->getServiceLocator());
    }
}
