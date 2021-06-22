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
    public function setUp()
    {
        $this->configPath       = sys_get_temp_dir() . '/api-tools-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter = new ConfigWriter();
    }

    public function tearDown()
    {
        //$this->removeConfigMocks();
    }

    public function createConfigMocks()
    {
        if (! is_dir($this->configPath)) {
            mkdir($this->configPath, 0775, true);
        }

        $contents = "<" . "?php\nreturn array();";
        file_put_contents($this->globalConfigPath, $contents);
    }

    public function removeConfigMocks()
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

    public function createModelFromConfigArray(array $global): ContentNegotiationModel
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $globalConfig = new ConfigResource($global, $this->globalConfigPath, $this->configWriter);
        return new ContentNegotiationModel($globalConfig);
    }

    public function assertContentConfigExists(string $contentName, array $config): void
    {
        $this->assertArrayHasKey('api-tools-content-negotiation', $config);
        $this->assertArrayHasKey('selectors', $config['api-tools-content-negotiation']);
        $this->assertArrayHasKey($contentName, $config['api-tools-content-negotiation']['selectors']);
        $this->assertInternalType('array', $config['api-tools-content-negotiation']['selectors'][$contentName]);
    }

    public function assertContentConfigEquals(array $expected, string $contentName, array $config): void
    {
        $this->assertContentConfigExists($contentName, $config);
        $config = $config['api-tools-content-negotiation']['selectors'][$contentName];
        $this->assertEquals($expected, $config);
    }

    public function assertContentConfigContains(array $expected, string $contentName, array $config): void
    {
        $this->assertContentConfigExists($contentName, $config);
        $config = $config['api-tools-content-negotiation']['selectors'][$contentName];
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $config);
            $this->assertEquals($value, $config[$key]);
        }
    }

    public function testCreateContentNegotiation()
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
        $this->assertContentConfigEquals($toCreate, 'Json', $global);
    }

    public function testUpdateContentNegotiation()
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
        $this->assertContentConfigEquals($toUpdate, 'Json', $global);
    }

    public function testRemoveContentNegotiation()
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
        $this->assertArrayNotHasKey('Json', $global['api-tools-content-negotiation']['selectors']);
    }

    public function testFetchAllContentNegotiation()
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
        $this->assertContentConfigContains($toCreate, 'Json', $global);
        $this->assertContentConfigContains($toCreate2, 'Foo', $global);

        $result = $model->fetchAll();
        $this->assertIsArray($result);
        foreach ($result as $value) {
            $this->assertInstanceOf(ContentNegotiationEntity::class, $value);
        }
    }

    public function testFetchContentNegotiation()
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
        $this->assertInstanceOf(ContentNegotiationEntity::class, $content);
        $arrayCopy = $content->getArrayCopy();
        $this->assertArrayHasKey('content_name', $arrayCopy);
        $this->assertEquals('Json', $arrayCopy['content_name']);
        $this->assertArrayHasKey('selectors', $arrayCopy);
        $this->assertEquals($toCreate, $arrayCopy['selectors']);
    }
}
