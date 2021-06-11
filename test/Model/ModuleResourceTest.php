<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\ModuleResource;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_diff;
use function class_exists;
use function dirname;
use function file_exists;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function preg_match;
use function preg_quote;
use function rmdir;
use function scandir;
use function spl_autoload_register;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class ModuleResourceTest extends TestCase
{
    /** @var string */
    private $modulePath;
    /** @var ModuleResource */
    private $resource;

    public function setUp(): void
    {
        $modules       = [];
        $moduleManager = $this->getMockBuilder(ModuleManager::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->modulePath = sprintf(
            '%s/%s',
            sys_get_temp_dir(),
            uniqid(str_replace('\\', '_', __NAMESPACE__) . '_')
        );
        mkdir($this->modulePath . '/config', 0775, true);

        $model = new ModuleModel(
            $moduleManager,
            [],
            []
        );

        $this->resource = new ModuleResource(
            $model,
            new ModulePathSpec(
                new ModuleUtils($moduleManager),
                'psr-0',
                $this->modulePath
            )
        );

        $this->seedApplicationConfig();
        $this->setupModuleAutoloader();
    }

    public function tearDown(): void
    {
        if ($this->modulePath && is_dir($this->modulePath)) {
            $this->removeDir($this->modulePath);
        }
    }

    public function seedApplicationConfig(): void
    {
        $contents = '<' . "?php\nreturn array(\n    'modules' => array(),\n);";
        file_put_contents($this->modulePath . '/config/application.config.php', $contents);
    }

    public function setupModuleAutoloader(): void
    {
        $modulePath = $this->modulePath;
        spl_autoload_register(function ($class) use ($modulePath) {
            if (! preg_match('/^(?P<namespace>.*?)' . preg_quote('\\') . 'Module$/', $class, $matches)) {
                return false;
            }
            $namespace = $matches['namespace'];
            $relPath   = str_replace('\\', '/', $namespace);
            $path      = sprintf('%s/module/%s/Module.php', $modulePath, $relPath);
            if (! file_exists($path)) {
                return false;
            }
            require_once $path;
            return class_exists($class, false);
        });
    }

    /**
     * Remove a directory even if not empty (recursive delete)
     */
    public function removeDir(string $dir): bool
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

    public function testCreateReturnsModuleWithVersion1(): void
    {
        $moduleName = uniqid('Foo');
        $module     = $this->resource->create([
            'name' => $moduleName,
        ]);
        self::assertInstanceOf(ModuleEntity::class, $module);
        self::assertEquals([1], $module->getVersions());
    }

    public function testCreateReturnsModuleWithSpecifiedVersion(): void
    {
        $moduleName = uniqid('Foo');
        $module     = $this->resource->create([
            'name'    => $moduleName,
            'version' => '2',
        ]);
        self::assertInstanceOf(ModuleEntity::class, $module);
        self::assertEquals([2], $module->getVersions());
    }

    public function testFetchNewlyCreatedModuleInjectsVersion(): void
    {
        $moduleName  = uniqid('Foo');
        $module      = $this->resource->create([
            'name' => $moduleName,
        ]);
        $moduleClass = $module->getNamespace() . '\Module';

        $modules       = [
            $moduleName => new $moduleClass(),
        ];
        $moduleManager = $this->getMockBuilder(ModuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moduleManager->expects($this->any())
            ->method('getLoadedModules')
            ->will($this->returnValue($modules));

        $model    = new ModuleModel(
            $moduleManager,
            [],
            []
        );
        $resource = new ModuleResource($model, new ModulePathSpec(new ModuleUtils($moduleManager)));
        $module   = $resource->fetch($moduleName);
        self::assertInstanceOf(ModuleEntity::class, $module);
        self::assertEquals([1], $module->getVersions());
    }

    public function testFetchModuleInjectsVersions(): void
    {
        $moduleName  = uniqid('Foo');
        $module      = $this->resource->create([
            'name' => $moduleName,
        ]);
        $moduleClass = $module->getNamespace() . '\Module';

        $r    = new ReflectionClass($moduleClass);
        $path = dirname($r->getFileName());
        mkdir(sprintf('%s/V2', $path), 0775, true);
        mkdir(sprintf('%s/V3', $path), 0775, true);

        $modules       = [
            $moduleName => new $moduleClass(),
        ];
        $moduleManager = $this->getMockBuilder(ModuleManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moduleManager->expects($this->any())
            ->method('getLoadedModules')
            ->will($this->returnValue($modules));

        $model    = new ModuleModel(
            $moduleManager,
            [],
            []
        );
        $resource = new ModuleResource($model, new ModulePathSpec(new ModuleUtils($moduleManager)));
        $module   = $resource->fetch($moduleName);
        self::assertInstanceOf(ModuleEntity::class, $module);
        self::assertEquals([1, 2, 3], $module->getVersions());
    }
}
