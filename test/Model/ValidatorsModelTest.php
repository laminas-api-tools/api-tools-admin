<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ValidatorMetadataModel;
use Laminas\ApiTools\Admin\Model\ValidatorsModel;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;

class ValidatorsModelTest extends TestCase
{
    protected $config;

    public function setUp()
    {
        $this->getConfig();
        $this->metadata = new ValidatorMetadataModel($this->config);
        $this->plugins  = new ValidatorPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->model    = new ValidatorsModel($this->plugins, $this->metadata);
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
        if (! array_key_exists('validator_metadata', $allConfig)) {
            $this->markTestSkipped('Module config file does not contain validator_metadata!');
        }

        $this->config = $allConfig['validator_metadata'];
        return $this->config;
    }

    public function testFetchAllReturnsListOfAvailablePlugins()
    {
        $validators  = $this->model->fetchAll();
        $this->assertGreaterThan(0, count($validators));
        foreach ($validators as $service => $metadata) {
            $this->assertContains('\\Validator\\', $service);
        }
    }

    public function testEachPluginIsAKeyArrayPair()
    {
        $validators  = $this->model->fetchAll();
        foreach ($this->model->fetchAll() as $service => $metadata) {
            $this->assertInternalType('string', $service);
            $this->assertInternalType('array', $metadata);
        }
    }
}
