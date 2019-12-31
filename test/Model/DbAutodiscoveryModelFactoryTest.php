<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel;
use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModelFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_TestCase as TestCase;

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

        $this->setExpectedException(ServiceNotCreatedException::class, 'config service is not present');
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
