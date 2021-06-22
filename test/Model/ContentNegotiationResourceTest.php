<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\InputFilter\ContentNegotiationInputFilter;
use Laminas\ApiTools\Admin\InputFilter\CreateContentNegotiationInputFilter;
use Laminas\ApiTools\Admin\Model\ContentNegotiationEntity;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Admin\Model\ContentNegotiationResource;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function unlink;

class ContentNegotiationResourceTest extends TestCase
{
    public function setUp()
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

    public function createModelFromConfigArray(array $global): ContentNegotiationModel
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $globalConfig = new ConfigResource($global, $this->globalConfigPath, $this->configWriter);
        return new ContentNegotiationModel($globalConfig);
    }

    public function createResourceFromConfigArray(array $global): ContentNegotiationResource
    {
        return new ContentNegotiationResource($this->createModelFromConfigArray($global));
    }

    public function testCreateShouldAcceptContentNameAndReturnNewEntity()
    {
        $data         = ['content_name' => 'Test'];
        $resource     = $this->createResourceFromConfigArray([]);
        $createFilter = new CreateContentNegotiationInputFilter();
        $createFilter->setData($data);
        $resource->setInputFilter($createFilter);

        $entity = $resource->create([]);

        $this->assertInstanceOf(ContentNegotiationEntity::class, $entity);
        $this->assertEquals('Test', $entity->name);
    }

    public function testUpdateShouldAcceptContentNameAndSelectorsAndReturnUpdatedEntity()
    {
        $data         = ['content_name' => 'Test'];
        $resource     = $this->createResourceFromConfigArray([]);
        $createFilter = new CreateContentNegotiationInputFilter();
        $createFilter->setData($data);
        $resource->setInputFilter($createFilter);

        $entity = $resource->create([]);

        $data         = [
            'selectors' => [
                ViewModel::class => [
                    'text/html',
                    'application/xhtml+xml',
                ],
            ],
        ];
        $updateFilter = new ContentNegotiationInputFilter();
        $updateFilter->setData($data);
        $resource->setInputFilter($updateFilter);

        $entity = $resource->patch('Test', []);
        $this->assertInstanceOf(ContentNegotiationEntity::class, $entity);
        $this->assertEquals('Test', $entity->name);
        $this->assertEquals($data['selectors'], $entity->config);
    }
}
