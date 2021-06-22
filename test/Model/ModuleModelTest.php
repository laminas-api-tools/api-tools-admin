<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase;
use Test;

use function array_diff;
use function array_map;
use function array_unique;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function preg_match;
use function range;
use function rename;
use function rmdir;
use function scandir;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use function var_export;

class ModuleModelTest extends TestCase
{
    /** @var string */
    public $modulePath;

    public function setUp()
    {
        if ($this->modulePath && file_exists($this->modulePath)) {
            $this->removeDir($this->modulePath);
            unset($this->modulePath);
        }

        $modules             = [
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Foa' => new TestAsset\Foa\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Foo' => new TestAsset\Foo\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar' => new TestAsset\Bar\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Baz' => new TestAsset\Baz\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bat' => new TestAsset\Bat\Module(),
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob' => new TestAsset\Bob\Module(),
        ];
        $this->moduleManager = $this->getMockBuilder(ModuleManager::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $restConfig = [
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Foo\Controller\Foo' => null, // this should never be returned
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Bar' => null,
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Baz' => null,
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bat\Controller\Bat' => null, // this should never be returned
        ];

        $rpcConfig = [
            // controller => empty pairs
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Foo\Controller\Act' => null, // this should never be returned
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Act' => null,
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Do'  => null,
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bat\Controller\Act' => null, // this should never be returned
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob\Controller\Do'  => null,
        ];

        $this->model = new ModuleModel(
            $this->moduleManager,
            $restConfig,
            $rpcConfig
        );
        $this->model->setUseShortArrayNotation(false);
    }

    public function tearDown()
    {
        if ($this->modulePath && file_exists($this->modulePath)) {
            $this->removeDir($this->modulePath);
            unset($this->modulePath);
        }
    }

    public function testEnabledModulesOnlyReturnsThoseThatImplementApiToolsProviderInterface()
    {
        $expected = [
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar',
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Baz',
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob',
        ];

        $modules = $this->model->getModules();

        // make sure we have the same number of modules
        $this->assertEquals(count($expected), count($modules));

        // Test that each module name exists in the expected list
        $moduleNames = [];
        foreach ($modules as $module) {
            $this->assertContains($module->getNamespace(), $expected);
            $moduleNames[] = $module->getNamespace();
        }

        // Test that we have all unique module names
        $test = array_unique($moduleNames);
        $this->assertEquals($moduleNames, $test);
    }

    /** @psalm-return array<array-key, array{0: string}> */
    public function invalidModules(): array
    {
        return [
            ['LaminasTest\ApiTools\Admin\Model\TestAsset\Foo'],
            ['LaminasTest\ApiTools\Admin\Model\TestAsset\Bat'],
        ];
    }

    /**
     * @dataProvider invalidModules
     */
    public function testNullIsReturnedWhenGettingServicesForNonApiToolsModules(string $module)
    {
        $this->assertNull($this->model->getModule($module));
    }

    public function testEmptyArraysAreReturnedWhenGettingServicesForApiToolsModulesWithNoServices()
    {
        $module = $this->model->getModule('LaminasTest\ApiTools\Admin\Model\TestAsset\Baz');
        $this->assertEquals([], $module->getRestServices());
        $this->assertEquals([], $module->getRpcServices());
    }

    public function testRestAndRpcControllersAreDiscoveredWhenGettingServicesForApiToolsModules()
    {
        $expected = [
            'rest' => [
                'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Bar',
                'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Baz',
            ],
            'rpc'  => [
                'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Act',
                'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Do',
            ],
        ];
        $module   = $this->model->getModule('LaminasTest\ApiTools\Admin\Model\TestAsset\Bar');
        $this->assertEquals($expected['rest'], $module->getRestServices());
        $this->assertEquals($expected['rpc'], $module->getRpcServices());
    }

    /**
     * @group listofservices
     */
    public function testCanRetrieveListOfAllApiToolsModulesAndTheirServices()
    {
        /* If this is running from a vendor directory, markTestSkipped() */
        if (preg_match('#[/\\\\]vendor[/\\\\]#', __FILE__)) {
            $this->markTestSkipped('Running from a vendor directory.');
        }

        $expected = [
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar' => [
                'vendor' => false,
                'rest'   => [
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Bar',
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Baz',
                ],
                'rpc'    => [
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Act',
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bar\Controller\Do',
                ],
            ],
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Baz' => [
                'vendor' => false,
                'rest'   => [],
                'rpc'    => [],
            ],
            'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob' => [
                'vendor' => false,
                'rest'   => [],
                'rpc'    => [
                    'LaminasTest\ApiTools\Admin\Model\TestAsset\Bob\Controller\Do',
                ],
            ],
        ];

        $modules = $this->model->getModules();

        $unique = [];
        foreach ($modules as $module) {
            $name = $module->getNamespace();
            $this->assertArrayHasKey(
                $name,
                $expected,
                sprintf('Failed asserting module "%s" is in list', $name)
            );
            $this->assertNotContains(
                $name,
                $unique,
                sprintf('Failed asserting module "%s" was not previously declared', $name)
            );
            $expectedMetadata = $expected[$name];
            $this->assertSame(
                $expectedMetadata['vendor'],
                $module->isVendor(),
                sprintf(
                    'Failed asserting module "%s" vendor flag matches "%s" (received "%s")',
                    $name,
                    var_export($expectedMetadata['vendor'], true),
                    var_export($module->isVendor(), true)
                )
            );
            $this->assertSame(
                $expectedMetadata['rest'],
                $module->getRestServices(),
                sprintf(
                    'Failed asserting module "%s" rest services match expectations; expected [ %s ], received [ %s ]',
                    $name,
                    var_export($expectedMetadata['rest'], true),
                    var_export($module->getRestServices(), true)
                )
            );
            $this->assertSame(
                $expectedMetadata['rpc'],
                $module->getRpcServices(),
                sprintf(
                    'Failed asserting module "%s" rpc services match expectations; expected [ %s ], received [ %s ]',
                    $name,
                    var_export($expectedMetadata['rpc'], true),
                    var_export($module->getRpcServices(), true)
                )
            );
            $unique[] = $name;
        }
    }

    /**
     * @dataProvider getModuleVersionDataProvider
     */
    public function testCreateModule(string $version)
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents("$modulePath/config/application.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec, $version));
        $this->assertTrue(file_exists("$modulePath/module/$module"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/V$version/Rpc"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/V$version/Rest"));
        $this->assertTrue(file_exists("$modulePath/module/$module/view"));
        $this->assertTrue(file_exists("$modulePath/module/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/config/module.config.php"));

        $this->removeDir($modulePath);
    }

    /**
     * @dataProvider getModuleVersionDataProvider
     * @group feature/psr4
     */
    public function testCreateModulePSR4(string $version)
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents("$modulePath/config/application.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath, 'psr-4');

        $this->assertTrue($this->model->createModule($module, $pathSpec, $version));
        $this->assertTrue(file_exists("$modulePath/module/$module"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/V$version/Rpc"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/V$version/Rest"));
        $this->assertTrue(file_exists("$modulePath/module/$module/view"));
        $this->assertTrue(file_exists("$modulePath/module/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/config/module.config.php"));

        $this->removeDir($modulePath);
    }

    /**
     * @return array
     */
    public function getModuleVersionDataProvider()
    {
        return array_map(function ($item) {
            return [$item];
        }, range(1, 10));
    }

    protected function getPathSpec(string $modulePath, string $spec = 'psr-0'): ModulePathSpec
    {
        return new ModulePathSpec(
            new ModuleUtils($this->moduleManager),
            $spec,
            $modulePath
        );
    }

    /**
     * @depends testCreateModule
     */
    public function testDeleteModule()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . '?php return array("modules" => array());'
        );

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));
        $config = include $modulePath . '/config/application.config.php';
        $this->assertArrayHasKey('modules', $config, var_export($config, true));

        // Now try and delete
        $this->assertTrue($this->model->deleteModule($module, $modulePath, false));

        $config = include $modulePath . '/config/application.config.php';
        $this->assertArrayHasKey('modules', $config, var_export($config, true));
        $this->assertNotContains($module, $config['modules']);
        $this->assertTrue(file_exists(sprintf('%s/module/%s', $modulePath, $module)));

        $this->removeDir($modulePath);
    }

    /**
     * @depends testCreateModule
     */
    public function testDeleteModuleRecursively()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . '?php return array("modules" => array());'
        );
        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));

        // Now try and delete
        $this->assertTrue($this->model->deleteModule($module, $modulePath, true));
        $this->assertFalse(
            file_exists(sprintf('%s/module/%s', $modulePath, $module)),
            'Module class found in tree when it not have been'
        );
    }

    /**
     * @group 22
     */
    public function testReturnFalseWhenTryingToCreateAModuleThatAlreadyExistsInConfiguration()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . "?php return array(\n    'modules' => array(\n        'Foo',\n    )\n);"
        );
        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertFalse($this->model->createModule($module, $pathSpec));
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
     * @return bool
     */
    protected function removeDir($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);
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
        $modules       = [
            'Test\Foo' => new Test\Foo\Module(),
            'Test\Bar' => new Test\Foo\Module(),
        ];
        $moduleManager = $this->getMockBuilder(ModuleManager::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue($modules));

        $model = new ModuleModel(
            $moduleManager,
            [],
            []
        );

        $modules = $model->getModules();
        foreach ($modules as $module) {
            $this->assertTrue($module->isVendor());
        }
    }

    public function testDefaultApiVersionIsSetProperly()
    {
        $modules       = [
            'Test\Bar' => new Test\Bar\Module(),
            'Test\Foo' => new Test\Foo\Module(),
        ];
        $moduleManager = $this->getMockBuilder(ModuleManager::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue($modules));

        $model = new ModuleModel(
            $moduleManager,
            [],
            []
        );

        $modules = $model->getModules();

        $this->assertSame(
            1,
            $modules[0]->getDefaultVersion(),
            'Did not default to version 1 as the default version for unconfigured default version of Test\Bar!'
        );
        $this->assertSame(
            123,
            $modules[1]->getDefaultVersion(),
            'Did not read configured default version 123 for Test\Foo!'
        );
    }

    /**
     * @group 59
     */
    public function testAttemptingToCreateModuleThatAlreadyExistsRaises409Exception()
    {
        $module           = 'Foo';
        $this->modulePath = $modulePath = sys_get_temp_dir()
          . "/"
          . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents("$modulePath/config/application.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));

        $this->expectException('Exception');
        $this->expectExceptionMessage('exists');
        $this->expectExceptionCode(409);

        $this->model->createModule($module, $pathSpec);
    }

    /**
     * @group 289
     */
    public function testWritesToModuleConfigFileOnModuleCreationWhenModuleConfigFileExists()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . '?php return array(\'modules\' => include __DIR__ . \'/modules.config.php\');'
        );
        file_put_contents("$modulePath/config/modules.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));
        $modules = include "$modulePath/config/modules.config.php";
        $this->assertInternalType('array', $modules);
        $this->assertContains('Foo', $modules);

        $this->removeDir($modulePath);
    }

    public function testWritesShortArrayNotationToApplicationModulesConfigurationWhenRequested()
    {
        $this->model->setUseShortArrayNotation(true);

        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . '?php return array(\'modules\' => include __DIR__ . \'/modules.config.php\');'
        );
        file_put_contents("$modulePath/config/modules.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));

        $contents = file_get_contents("$modulePath/config/modules.config.php");
        $this->assertNotContains('array(', $contents);
        $this->assertContains('return [', $contents);
    }

    public function testWritesShortArrayNotationToNewModuleConfigurationWhenRequested()
    {
        $this->model->setUseShortArrayNotation(true);

        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . '?php return array(\'modules\' => include __DIR__ . \'/modules.config.php\');'
        );
        file_put_contents("$modulePath/config/modules.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));

        $contents = file_get_contents("$modulePath/module/Foo/config/module.config.php");
        $this->assertNotContains('array(', $contents);
        $this->assertContains('return [', $contents);
    }
}
