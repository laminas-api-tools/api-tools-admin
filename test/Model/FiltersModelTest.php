<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\FiltersModel;
use Laminas\Filter\FilterPluginManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function array_key_exists;
use function array_keys;
use function count;
use function file_exists;
use function is_array;
use function sprintf;
use function var_export;

class FiltersModelTest extends TestCase
{
    use ProphecyTrait;

    /** @var array<string, mixed> */
    protected $config;

    /** @var FilterPluginManager */
    private $plugins;

    /** @var FiltersModel */
    private $model;

    public function setUp(): void
    {
        $this->config  = $this->getConfig();
        $this->plugins = new FilterPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->model   = new FiltersModel($this->plugins, $this->config);
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
        if (! array_key_exists('filter_metadata', $allConfig)) {
            $this->markTestSkipped('Module config file does not contain filter_metadata!');
        }

        $this->config = $allConfig['filter_metadata'];
        return $this->config;
    }

    public function testFetchAllReturnsListOfAvailablePlugins(): void
    {
        $filters = $this->model->fetchAll();
        self::assertGreaterThan(0, count($filters));
        foreach (array_keys($filters) as $service) {
            self::assertStringContainsString('\\Filter\\', $service);
        }
    }

    public function testEachPluginIsAKeyArrayPair(): void
    {
        $filters = $this->model->fetchAll();
        foreach ($filters as $service => $metadata) {
            self::assertIsString($service);
            self::assertIsArray(
                $metadata,
                sprintf('Key "%s" does not have array metadata: "%s"', $service, var_export($metadata, true))
            );
        }
    }
}
