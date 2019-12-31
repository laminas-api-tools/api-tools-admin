<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ModuleModel;
use PHPUnit_Framework_TestCase as TestCase;
use Test;

class ModuleModelTest extends TestCase
{
    public function setUp()
    {
        $modules = array(
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Foa' => new TestAsset\Foa\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Foo' => new TestAsset\Foo\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar' => new TestAsset\Bar\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Baz' => new TestAsset\Baz\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bat' => new TestAsset\Bat\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob' => new TestAsset\Bob\Module(),
        );
        $this->moduleManager = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $restConfig           = array(
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Foo\Controller\Foo' => null, // this should never be returned
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Bar' => null,
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Baz' => null,
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bat\Controller\Bat' => null, // this should never be returned
        );

        $rpcConfig          = array(
            // controller => empty pairs
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Foo\Controller\Act' => null, // this should never be returned
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Act' => null,
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Do'  => null,
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bat\Controller\Act' => null, // this should never be returned
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob\Controller\Do'  => null,
        );

        $this->model         = new ModuleModel($this->moduleManager, $restConfig, $rpcConfig);
    }

    public function testEnabledModulesOnlyReturnsThoseThatImplementApiToolsModuleInterface()
    {
        $expected = array(
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar',
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Baz',
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob',
        );

        $modules = $this->model->getModules();

        // make sure we have the same number of modules
        $this->assertEquals(count($expected), count($modules));

        // Test that each module name exists in the expected list
        $moduleNames = array();
        foreach ($modules as $module) {
            $this->assertContains($module->getNamespace(), $expected);
            $moduleNames[] = $module->getNamespace();
        }

        // Test that we have all unique module names
        $test = array_unique($moduleNames);
        $this->assertEquals($moduleNames, $test);
    }

    public function invalidModules()
    {
        return array(
            array('LaminasTest\ApiTools\Admin\Model\TestAsset\Foo'),
            array('LaminasTest\ApiTools\Admin\Model\TestAsset\Bat'),
        );
    }

    /**
     * @dataProvider invalidModules
     */
    public function testNullIsReturnedWhenGettingServicesForNonApiToolsModules($module)
    {
        $this->assertNull($this->model->getModule($module));
    }

    public function testEmptyArraysAreReturnedWhenGettingServicesForApiToolsModulesWithNoServices()
    {
        $module = $this->model->getModule('LaminasTest\ApiTools\Admin\Model\TestAsset\Baz');
        $this->assertEquals(array(), $module->getRestServices());
        $this->assertEquals(array(), $module->getRpcServices());
    }

    public function testRestAndRpcControllersAreDiscoveredWhenGettingServicesForApiToolsModules()
    {
        $expected = array(
            'rest' => array(
                'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Bar',
                'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Baz',
            ),
            'rpc' => array(
                'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Act',
                'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Do',
            ),
        );
        $module = $this->model->getModule('LaminasTest\ApiTools\Admin\Model\TestAsset\Bar');
        $this->assertEquals($expected['rest'], $module->getRestServices());
        $this->assertEquals($expected['rpc'], $module->getRpcServices());
    }

    public function testCanRetrieveListOfAllApiToolsModulesAndTheirServices()
    {
        $expected = array(
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar' => array(
                'vendor' => false,
                'rest' => array(
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Bar',
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Baz',
                ),
                'rpc' => array(
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Act',
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Do',
                ),
            ),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Baz' => array(
                'vendor' => false,
                'rest' => array(),
                'rpc'  => array(),
            ),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob' => array(
                'vendor' => false,
                'rest' => array(
                ),
                'rpc' => array(
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob\Controller\Do',
                ),
            ),
        );

        $modules = $this->model->getModules();

        $unique  = array();
        foreach ($modules as $module) {
            $name = $module->getNamespace();
            $this->assertArrayHasKey($name, $expected);
            $this->assertNotContains($name, $unique);
            $expectedMetadata = $expected[$name];
            $this->assertSame($expectedMetadata['vendor'], $module->isVendor());
            $this->assertSame($expectedMetadata['rest'], $module->getRestServices());
            $this->assertSame($expectedMetadata['rpc'], $module->getRpcServices());
            $unique[] = $name;
        }
    }

    public function testCreateModule()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(__NAMESPACE__ . '_');

        mkdir("$modulePath/module", 0777, true);
        mkdir("$modulePath/config", 0777, true);
        file_put_contents("$modulePath/config/application.config.php", '<' . '?php return array();');

        $this->assertTrue($this->model->createModule($module, $modulePath));
        $this->assertTrue(file_exists("$modulePath/module/$module"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/V1/Rpc"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/V1/Rest"));
        $this->assertTrue(file_exists("$modulePath/module/$module/view"));
        $this->assertTrue(file_exists("$modulePath/module/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/config/module.config.php"));

        $this->removeDir($modulePath);
        return true;
    }

    public function testUpdateExistingApiModule()
    {
        $module = 'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar';
        $this->assertFalse($this->model->updateModule($module));
    }

    public function testUpdateModule()
    {
        $module = 'LaminasTest\ApiTools\Admin\Model\TestAsset\Foo';
        $this->assertTrue($this->model->updateModule($module));

        unlink(__DIR__ . '/TestAsset/Foo/Module.php');
        rename(
            __DIR__ . '/TestAsset/Foo/Module.php.old',
            __DIR__ . '/TestAsset/Foo/Module.php'
        );
    }

    public function testUpdateModuleWithOtherInterfaces()
    {
        $module = 'LaminasTest\ApiTools\Admin\Model\TestAsset\Foa';
        $this->assertTrue($this->model->updateModule($module));

        unlink(__DIR__ . '/TestAsset/Foa/Module.php');
        rename(
            __DIR__ . '/TestAsset/Foa/Module.php.old',
            __DIR__ . '/TestAsset/Foa/Module.php'
        );
    }

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

    public function testVendorModulesAreMarkedAccordingly()
    {
        $modules = array(
            'Test\Foo' => new Test\Foo\Module(),
            'Test\Bar' => new Test\Foo\Module(),
        );
        $moduleManager = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
                              ->disableOriginalConstructor()
                              ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue($modules));

        $model = new ModuleModel($moduleManager, array(), array());

        $modules = $model->getModules();
        foreach ($modules as $module) {
            $this->assertTrue($module->isVendor());
        }
    }
}