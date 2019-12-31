<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;

abstract class AbstractPluginManagerModelTest extends TestCase
{
    public $model;
    public $namespace;
    public $plugins;

    public function setUp()
    {
        $this->markTestIncomplete(
            'Please define the setUp() method in your extending test case,'
            . ' and set the plugins, model, and namespace properties'
        );
    }

    public function testFetchAllReturnsListOfAvailablePlugins()
    {
        $services = $this->model->fetchAll();
        $this->assertGreaterThan(0, count($services));
        foreach ($services as $service) {
            $this->assertContains($this->namespace, $service);
        }
    }
}
