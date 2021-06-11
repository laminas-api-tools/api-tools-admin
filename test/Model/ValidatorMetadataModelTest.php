<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ValidatorMetadataModel;
use PHPUnit\Framework\TestCase;

use function array_key_exists;
use function array_keys;
use function file_exists;
use function is_array;

class ValidatorMetadataModelTest extends TestCase
{
    /** @var array<string, mixed> */
    protected $config;
    /** @var ValidatorMetadataModel */
    private $model;

    public function setUp(): void
    {
        $this->getConfig();
        $this->model = new ValidatorMetadataModel($this->config);
    }

    /** @return array<string, mixed> */
    public function getConfig(): array
    {
        if (is_array($this->config)) {
            return $this->config;
        }

        $configFile = __DIR__ . '/../../config/module.config.php';
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

    /** @param array<string, mixed> $metadata */
    public function assertDefaultOptions(array $metadata): void
    {
        foreach (array_keys($this->config['__all__']) as $key) {
            self::assertArrayHasKey($key, $metadata);
        }
    }

    /** @psalm-return array<string, array{0: string}> */
    public function allPlugins(): array
    {
        $return = [];
        foreach (array_keys($this->getConfig()) as $plugin) {
            if ('__all__' === $plugin) {
                continue;
            }
            $return[$plugin] = [$plugin];
        }
        return $return;
    }

    /**
     * @dataProvider allPlugins
     */
    public function testAllPluginsContainDefaultOptions(string $plugin): void
    {
        $metadata = $this->model->fetch($plugin);
        self::assertIsArray($metadata);
        self::assertDefaultOptions($metadata);
    }

    /**
     * @dataProvider allPlugins
     */
    public function testCanFetchAllMetadataAtOnce(string $plugin): void
    {
        $metadata = $this->model->fetchAll();
        self::assertIsArray($metadata);
        self::assertArrayHasKey($plugin, $metadata);
    }

    /**
     * @dataProvider allPlugins
     */
    public function testEachPluginInAllMetadataContainsDefaultOptions(string $plugin): void
    {
        $metadata = $this->model->fetchAll();
        self::assertIsArray($metadata);
        self::assertArrayHasKey($plugin, $metadata);

        $metadata = $metadata[$plugin];
        self::assertIsArray($metadata);
        self::assertDefaultOptions($metadata);
    }

    public function testFetchingAllMetadataOmitsMagicAllKey(): void
    {
        $metadata = $this->model->fetchAll();
        self::assertIsArray($metadata);
        self::assertArrayNotHasKey('__all__', $metadata);
    }
}
