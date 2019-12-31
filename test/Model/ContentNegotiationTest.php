<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use PHPUnit\Framework\TestCase;

class ContentNegotiationTest extends TestCase
{
    public function setUp()
    {
        $this->configPath       = sys_get_temp_dir() . '/api-tools-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter     = new ConfigWriter();
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

    public function createModelFromConfigArray(array $global)
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $globalConfig = new ConfigResource($global, $this->globalConfigPath, $this->configWriter);
        return new ContentNegotiationModel($globalConfig);
    }

    public function assertContentConfigExists($contentName, array $config)
    {
        $this->assertArrayHasKey('api-tools-content-negotiation', $config);
        $this->assertArrayHasKey('selectors', $config['api-tools-content-negotiation']);
        $this->assertArrayHasKey($contentName, $config['api-tools-content-negotiation']['selectors']);
        $this->assertInternalType('array', $config['api-tools-content-negotiation']['selectors'][$contentName]);
    }

    public function assertContentConfigEquals(array $expected, $contentName, array $config)
    {
        $this->assertContentConfigExists($contentName, $config);
        $config = $config['api-tools-content-negotiation']['selectors'][$contentName];
        $this->assertEquals($expected, $config);
    }

    public function assertContentConfigContains(array $expected, $contentName, array $config)
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
            'Laminas\ApiTools\ContentNegotiation\JsonModel' => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $global = include $this->globalConfigPath;
        $this->assertContentConfigEquals($toCreate, 'Json', $global);
    }

    public function testUpdateContentNegotiation()
    {
        $toCreate = [
           'Laminas\ApiTools\ContentNegotiation\JsonModel' => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $toUpdate = [
            'Laminas\ApiTools\ContentNegotiation\JsonModel' => [
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
           'Laminas\ApiTools\ContentNegotiation\JsonModel' => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $model->remove('Json');
        $global = include $this->globalConfigPath;
        $this->assertArrayNotHasKey('Json', $global['api-tools-content-negotiation']['selectors']);
    }

    public function testFetchAllContentNegotiation()
    {
        $toCreate = [
            'Laminas\ApiTools\ContentNegotiation\JsonModel' => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model = $this->createModelFromConfigArray([]);
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
        $this->assertInternalType('array', $result);
        foreach ($result as $value) {
            $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\ContentNegotiationEntity', $value);
        }
    }

    public function testFetchContentNegotiation()
    {
        $toCreate = [
            'Laminas\ApiTools\ContentNegotiation\JsonModel' => [
                'application/json',
                'application/*+json',
            ],
        ];
        $model = $this->createModelFromConfigArray([]);
        $model->create('Json', $toCreate);

        $content = $model->fetch('Json');
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\ContentNegotiationEntity', $content);
        $arrayCopy = $content->getArrayCopy();
        $this->assertArrayHasKey('content_name', $arrayCopy);
        $this->assertEquals('Json', $arrayCopy['content_name']);
        $this->assertArrayHasKey('selectors', $arrayCopy);
        $this->assertEquals($toCreate, $arrayCopy['selectors']);
    }
}
