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
    public $plugins;
    public $model;

    public function setUp()
    {
        $this->markTestIncomplete(
            'Please define the setUp() method in your extending test case, and set the plugins and model properties'
        );
    }

    public function testFetchAllReturnsListOfAvailablePlugins()
    {
        $allServices = $this->plugins->getRegisteredServices();
        $validators  = [];
        foreach ($allServices as $key => $services) {
            $validators += $services;
        }
        sort($validators, SORT_STRING);

        $this->assertEquals($validators, $this->model->fetchAll());
    }
}
