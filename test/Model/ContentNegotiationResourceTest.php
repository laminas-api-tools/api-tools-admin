<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\InputFilter\ContentNegotiationInputFilter;
use Laminas\ApiTools\Admin\InputFilter\CreateContentNegotiationInputFilter;
use Laminas\ApiTools\Admin\Model\ContentNegotiationModel;
use Laminas\ApiTools\Admin\Model\ContentNegotiationResource;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use PHPUnit_Framework_TestCase as TestCase;

class ContentNegotiationResourceTest extends TestCase
{
    public function setUp()
    {
        $this->configPath       = sys_get_temp_dir() . '/api-tools-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter     = new ConfigWriter();
    }

    public function createConfigMocks()
    {
        if (!is_dir($this->configPath)) {
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

    public function createResourceFromConfigArray(array $global)
    {
        return new ContentNegotiationResource($this->createModelFromConfigArray($global));
    }


    public function testCreateShouldAcceptContentNameAndReturnNewEntity()
    {
        $data = ['content_name' => 'Test'];
        $resource = $this->createResourceFromConfigArray([]);
        $createFilter = new CreateContentNegotiationInputFilter();
        $createFilter->setData($data);
        $resource->setInputFilter($createFilter);

        $entity = $resource->create([]);

        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\ContentNegotiationEntity', $entity);
        $this->assertEquals('Test', $entity->name);
    }

    public function testUpdateShouldAcceptContentNameAndSelectorsAndReturnUpdatedEntity()
    {
        $data = ['content_name' => 'Test'];
        $resource = $this->createResourceFromConfigArray([]);
        $createFilter = new CreateContentNegotiationInputFilter();
        $createFilter->setData($data);
        $resource->setInputFilter($createFilter);

        $entity = $resource->create([]);

        $data = ['selectors' => [
            'Laminas\View\Model\ViewModel' => [
                'text/html',
                'application/xhtml+xml',
            ],
        ]];
        $updateFilter = new ContentNegotiationInputFilter();
        $updateFilter->setData($data);
        $resource->setInputFilter($updateFilter);

        $entity = $resource->patch('Test', []);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\ContentNegotiationEntity', $entity);
        $this->assertEquals('Test', $entity->name);
        $this->assertEquals($data['selectors'], $entity->config);
    }
}
