<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\AbstractPluginManagerModel;
use Laminas\ServiceManager\AbstractPluginManager;
use PHPUnit\Framework\TestCase;

use function count;

abstract class AbstractPluginManagerModelTest extends TestCase
{
    /** @var AbstractPluginManagerModel */
    public $model;

    /** @var string */
    public $namespace;

    /** @var AbstractPluginManager */
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
