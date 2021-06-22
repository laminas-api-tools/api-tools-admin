<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\ValidatorMetadataModel;
use Laminas\ApiTools\Admin\Model\ValidatorsModel;
use Laminas\Validator\ValidatorPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function array_key_exists;
use function array_keys;
use function count;
use function file_exists;
use function is_array;

class ValidatorsModelTest extends TestCase
{
    use ProphecyTrait;

    /** @var array<string, mixed> */
    protected $config;

    /** @var ValidatorMetadataModel */
    private $metadata;

    /** @var ValidatorPluginManager */
    private $plugins;

    /** @var ValidatorsModel */
    private $model;

    public function setUp(): void
    {
        $this->getConfig();
        $this->metadata = new ValidatorMetadataModel($this->config);
        $this->plugins  = new ValidatorPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->model    = new ValidatorsModel($this->plugins, $this->metadata);
    }

    /** @return array<string, mixed> */
    public function getConfig(): array
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

    public function testFetchAllReturnsListOfAvailablePlugins(): void
    {
        $validators = $this->model->fetchAll();
        self::assertGreaterThan(0, count($validators));
        foreach (array_keys($validators) as $service) {
            self::assertStringContainsString('\\Validator\\', $service);
        }
    }

    public function testEachPluginIsAKeyArrayPair(): void
    {
        foreach ($this->model->fetchAll() as $service => $metadata) {
            self::assertIsString($service);
            self::assertIsArray($metadata);
        }
    }
}
