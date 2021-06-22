<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ContentNegotiationEntity;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\ContentNegotiation\JsonModel;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

class ContentNegotiationTest extends TestCase
{
    /** @var string */
    private $configPath;
    /** @var string */
    private $globalConfigPath;
    /** @var ConfigWriter */
    private $configWriter;

    public function setUp(): void
    {
        $this->configPath       = sys_get_temp_dir() . '/api-tools-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter = new ConfigWriter();
    }

    public function createConfigMocks(): void
    {
        if (! is_dir($this->configPath)) {
            mkdir($this->configPath, 0775, true);
        }

        $contents = "<" . "?php\nreturn array();";
        file_put_contents($this->globalConfigPath, $contents);
    }

    public function removeConfigMocks(): void
    {
        if (file_exists($this->globalConfigPath)) {
            unlink($this->globalConfigPath);
        }
        if (is_dir($this->configPath)) {
            rmdir($this->configPath);
        }
        if (is_dir(dirname($this->configPath))) {
            rmdir(dirname($this->configPath));
        }
    }

    /**
     * @param array<string, mixed> $global
     */
    public function createModelFromConfigArray(array $global): ContentNegotiationModel
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $globalConfig = new ConfigResource($global, $this->globalConfigPath, $this->configWriter);
        return new ContentNegotiationModel($globalConfig);
    }

    /** @param array<string, mixed> $config */
    public function assertContentConfigExists(string $contentName, array $config): void
    {
        self::assertArrayHasKey('api-tools-content-negotiation', $config);
        self::assertArrayHasKey('selectors', $config['api-tools-content-negotiation']);
        self::assertArrayHasKey($contentName, $config['api-tools-content-negotiation']['selectors']);
        self::assertIsArray($config['api-tools-content-negotiation']['selectors'][$contentName]);
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $config
     */
    public function assertContentConfigEquals(array $expected, string $contentName, array $config): void
    {
        self::assertContentConfigExists($contentName, $config);
        $config = $config['api-tools-content-negotiation']['selectors'][$contentName];
        self::assertEquals($expected, $config);
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $config
     */
    public function assertContentConfigContains(array $expected, string $contentName, array $config): void
    {
        self::assertContentConfigExists($contentName, $config);
        $config = $config['api-tools-content-negotiation']['selectors'][$contentName];
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $config);
            self::assertEquals($value, $config[$key]);
        }
    }

    public function testCreateContentNegotiation(): void
    {
        $toCreate = [
            JsonModel::class => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model    = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $global = include $this->globalConfigPath;
        self::assertContentConfigEquals($toCreate, 'Json', $global);
    }

    public function testUpdateContentNegotiation(): void
    {
        $toCreate = [
            JsonModel::class => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model    = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $toUpdate = [
            JsonModel::class => [
                'application/json',
            ],
        ];
        $model->update('Json', $toUpdate);
        $global = include $this->globalConfigPath;
        self::assertContentConfigEquals($toUpdate, 'Json', $global);
    }

    public function testRemoveContentNegotiation(): void
    {
        $toCreate = [
            JsonModel::class => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model    = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $model->remove('Json');
        $global = include $this->globalConfigPath;
        self::assertArrayNotHasKey('Json', $global['api-tools-content-negotiation']['selectors']);
    }

    public function testFetchAllContentNegotiation(): void
    {
        $toCreate = [
            JsonModel::class => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model    = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $toCreate2 = [
            'Laminas\ApiTools\ContentNegotiation\FooModel' => [
                'application/foo',
            ],
        ];
        $model->create('Foo', $toCreate2);

        $global = include $this->globalConfigPath;
        self::assertContentConfigContains($toCreate, 'Json', $global);
        self::assertContentConfigContains($toCreate2, 'Foo', $global);

        $result = $model->fetchAll();
        self::assertIsArray($result);
        foreach ($result as $value) {
            self::assertInstanceOf(ContentNegotiationEntity::class, $value);
        }
    }

    public function testFetchContentNegotiation(): void
    {
        $toCreate = [
            JsonModel::class => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model    = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $content = $model->fetch('Json');
        self::assertInstanceOf(ContentNegotiationEntity::class, $content);
        $arrayCopy = $content->getArrayCopy();
        self::assertArrayHasKey('content_name', $arrayCopy);
        self::assertEquals('Json', $arrayCopy['content_name']);
        self::assertArrayHasKey('selectors', $arrayCopy);
        self::assertEquals($toCreate, $arrayCopy['selectors']);
    }
}
