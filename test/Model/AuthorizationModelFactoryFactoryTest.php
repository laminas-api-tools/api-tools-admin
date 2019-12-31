<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\AuthorizationModelFactory;
use Laminas\ApiTools\Admin\Model\AuthorizationModelFactoryFactory;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Configuration\ConfigResourceFactory;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use PHPUnit_Framework_TestCase as TestCase;

class AuthorizationModelFactoryFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function missingDependencies()
    {
        return [
            'all' => [[
                ModulePathSpec::class => false,
                ConfigResourceFactory::class => false,
                ModuleModel::class => false,
            ]],
            'ModulePathSpec' => [[
                ModulePathSpec::class => false,
                ConfigResourceFactory::class => true,
                ModuleModel::class => true,
            ]],
            'ConfigResourceFactory' => [[
                ModulePathSpec::class => true,
                ConfigResourceFactory::class => false,
                ModuleModel::class => true,
            ]],
            'ModuleModel' => [[
                ModulePathSpec::class => true,
                ConfigResourceFactory::class => true,
                ModuleModel::class => false,
            ]],
            'ModulePathSpec + ConfigResourceFactory' => [[
                ModulePathSpec::class => false,
                ConfigResourceFactory::class => false,
                ModuleModel::class => true,
            ]],
            'ModulePathSpec + ModuleModel' => [[
                ModulePathSpec::class => false,
                ConfigResourceFactory::class => true,
                ModuleModel::class => false,
            ]],
            'ConfigResourceFactory + ModuleModel' => [[
                ModulePathSpec::class => true,
                ConfigResourceFactory::class => false,
                ModuleModel::class => false,
            ]],
        ];
    }

    /**
     * @dataProvider missingDependencies
     */
    public function testFactoryRaisesExceptionIfAnyDependenciesAreMissing(array $dependencies)
    {
        $factory = new AuthorizationModelFactoryFactory;

        foreach ($dependencies as $dependency => $presence) {
            $this->container->has($dependency)->willReturn($presence);
        }

        $this->setExpectedException(ServiceNotCreatedException::class, 'missing one or more dependencies');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsAuthorizatoinModelFactory()
    {
        $factory = new AuthorizationModelFactoryFactory;
        $modulePathSpec = $this->prophesize(ModulePathSpec::class);
        $configResourceFactory = $this->prophesize(ResourceFactory::class);
        $moduleModel = $this->prophesize(ModuleModel::class);

        $this->container->has(ModulePathSpec::class)->willReturn(true);
        $this->container->get(ModulePathSpec::class)->will([$modulePathSpec, 'reveal']);
        $this->container->has(ConfigResourceFactory::class)->willReturn(true);
        $this->container->get(ConfigResourceFactory::class)->will([$configResourceFactory, 'reveal']);
        $this->container->has(ModuleModel::class)->willReturn(true);
        $this->container->get(ModuleModel::class)->will([$moduleModel, 'reveal']);

        $modelFactory = $factory($this->container->reveal());

        $this->assertInstanceOf(AuthorizationModelFactory::class, $modelFactory);
        $this->assertAttributeSame($modulePathSpec->reveal(), 'modules', $modelFactory);
        $this->assertAttributeSame($configResourceFactory->reveal(), 'configFactory', $modelFactory);
        $this->assertAttributeSame($moduleModel->reveal(), 'moduleModel', $modelFactory);
    }
}
