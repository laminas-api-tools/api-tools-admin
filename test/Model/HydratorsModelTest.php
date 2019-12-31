<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\HydratorsModel;
use Laminas\Hydrator\HydratorPluginManager;

class HydratorsModelTest extends AbstractPluginManagerModelTest
{
    public function setUp()
    {
        $this->namespace = '\\Hydrator\\';
        $this->plugins = new HydratorPluginManager();
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
