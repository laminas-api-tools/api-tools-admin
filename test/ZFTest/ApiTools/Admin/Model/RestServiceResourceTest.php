<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use BarConf;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\RestServiceEntity;
use Laminas\ApiTools\Admin\Model\RestServiceModel;
use Laminas\ApiTools\Admin\Model\RestServiceModelFactory;
use Laminas\ApiTools\Admin\Model\RestServiceResource;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\Config\Writer\PhpArray;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionObject;

require_once __DIR__ . '/TestAsset/module/BarConf/Module.php';

class RestServiceResourceTest extends TestCase
{
    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return boolean
     */
    protected function removeDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    protected function cleanUpAssets()
    {
        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
        $configPath = $basePath . '/config';
        $srcPath    = $basePath . '/src';
        if (is_dir($srcPath)) {
            $this->removeDir($srcPath);
        }
        copy($configPath . '/module.config.php.dist', $configPath . '/module.config.php');
    }

    public function setUp()
    {
        $this->module = 'BarConf';
        $this->cleanUpAssets();

        $modules = array(
            'BarConf' => new BarConf\Module()
        );

        $this->moduleEntity = new ModuleEntity($this->module, array(), array(), false);

        $this->moduleManager = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer        = new PhpArray();
        $this->modules       = new ModuleUtils($this->moduleManager);
        $this->configFactory = new ResourceFactory($this->modules, $this->writer);
        $config = $this->configFactory->factory('BarConf');

        $this->restServiceModel = new RestServiceModel($this->moduleEntity, $this->modules, $config);

        $this->restServiceModelFactory = $this->getMockBuilder('Laminas\ApiTools\Admin\Model\RestServiceModelFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->restServiceModelFactory
            ->expects($this->any())
            ->method('factory')
            ->with($this->equalTo('BarConf'), $this->equalTo(RestServiceModelFactory::TYPE_DEFAULT))
            ->will($this->returnValue($this->restServiceModel));


        $this->filter        = $this->getMockBuilder('Laminas\ApiTools\Admin\Model\InputFilterModel')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->docs          = $this->getMockBuilder('Laminas\ApiTools\Admin\Model\DocumentationModel')
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->resource      = new RestServiceResource($this->restServiceModelFactory, $this->filter, $this->docs);

        $r = new ReflectionObject($this->resource);
        $prop = $r->getProperty('moduleName');
        $prop->setAccessible(true);
        $prop->setValue($this->resource, 'BarConf');
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility/issues/18
     */
    public function testCreateReturnsRestServiceEntityWithControllerServiceNamePopulated()
    {
        $entity = $this->resource->create(array('resource_name' => 'test'));
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RestServiceEntity', $entity);
        $controllerServiceName = $entity->controllerServiceName;
        $this->assertNotEmpty($controllerServiceName);
        $this->assertContains('\\Test\\', $controllerServiceName);
    }
}
