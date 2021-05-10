<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\HydratorsModel;
use Laminas\Hydrator\HydratorPluginManager;

class HydratorsModelTest extends AbstractPluginManagerModelTest
{
    public function setUp()
    {
        $this->namespace = '\\Hydrator\\';
        $this->plugins = new HydratorPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->model = new HydratorsModel($this->plugins);
    }

    public function testFetchAllReturnsListOfAvailablePlugins()
    {
        $services = $this->model->fetchAll();
        $this->assertGreaterThan(-1, count($services));
        foreach ($services as $service) {
            $this->assertContains($this->namespace, $service);
        }
    }
}
