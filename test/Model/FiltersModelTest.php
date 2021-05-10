<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\FiltersModel;
use Laminas\Filter\FilterPluginManager;
use PHPUnit\Framework\TestCase;

class FiltersModelTest extends TestCase
{
    protected $config;

    public function setUp()
    {
        $this->config  = $this->getConfig();
        $this->plugins = new FilterPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->model   = new FiltersModel($this->plugins, $this->config);
    }

    public function getConfig()
    {
        if (is_array($this->config)) {
            return $this->config;
        }

        $configFile = __DIR__ . '/../../../../../config/module.config.php';
        if (! file_exists($configFile)) {
            $this->markTestSkipped('Cannot find module config file!');
        }
        $allConfig = include $configFile;
        if (! array_key_exists('filter_metadata', $allConfig)) {
            $this->markTestSkipped('Module config file does not contain filter_metadata!');
        }

        $this->config = $allConfig['filter_metadata'];
        return $this->config;
    }

    public function testFetchAllReturnsListOfAvailablePlugins()
    {
        $filters = $this->model->fetchAll();
        $this->assertGreaterThan(0, count($filters));
        foreach ($filters as $service => $metadata) {
            $this->assertContains('\\Filter\\', $service);
        }
    }

    public function testEachPluginIsAKeyArrayPair()
    {
        $filters = $this->model->fetchAll();
        foreach ($filters as $service => $metadata) {
            $this->assertInternalType('string', $service);
            $this->assertInternalType(
                'array',
                $metadata,
                sprintf('Key "%s" does not have array metadata: "%s"', $service, var_export($metadata, 1))
            );
        }
    }
}
