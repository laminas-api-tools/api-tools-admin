<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\AbstractPluginManagerModel;
use Laminas\ServiceManager\PluginManagerInterface;
use PHPUnit\Framework\TestCase;

use function count;

abstract class AbstractPluginManagerModelTest extends TestCase
{
    /** @var AbstractPluginManagerModel */
    public $model;

    /** @var string */
    public $namespace;

    /** @var PluginManagerInterface */
    public $plugins;

    public function setUp(): void
    {
        $this->markTestIncomplete(
            'Please define the setUp() method in your extending test case,'
            . ' and set the plugins, model, and namespace properties'
        );
    }

    public function testFetchAllReturnsListOfAvailablePlugins(): void
    {
        $services = $this->model->fetchAll();
        self::assertGreaterThan(0, count($services));
        foreach ($services as $service) {
            self::assertStringContainsString($this->namespace, $service);
        }
    }
}
