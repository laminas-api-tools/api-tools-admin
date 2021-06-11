<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Controller;

use FilesystemIterator;
use Laminas\ApiTools\Admin\Controller\FsPermissionsController;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function chdir;
use function file_exists;
use function getcwd;
use function is_dir;
use function is_writable;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class FsPermissionsControllerTest extends TestCase
{
    /** @var false|string */
    private $pwd;
    /** @var string */
    private $wd;
    /** @var FsPermissionsController */
    private $controller;

    public function setUp(): void
    {
        $this->pwd = getcwd();
        $this->wd  = sys_get_temp_dir() . '/ag-admin-' . uniqid();
        mkdir($this->wd);
        chdir($this->wd);

        $this->controller = new FsPermissionsController();
    }

    public function tearDown(): void
    {
        chdir($this->pwd);
        $this->removeDir($this->wd);
    }

    public function removeDir(string $directory): void
    {
        foreach (
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $directory,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::CHILD_FIRST
            ) as $path
        ) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }
        rmdir($directory);
    }

    public function testReturnsTrueIfNeitherConfigNorModuleDirectoriesExistButRootIsWritable(): void
    {
        $result = $this->controller->fsPermissionsAction();
        self::assertInstanceOf(ViewModel::class, $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        self::assertNotNull($fsPerms);
        self::assertTrue($fsPerms);
    }

    public function testReturnsTrueIfConfigAndModuleDirectoriesExistAndAreWritable(): void
    {
        mkdir($this->wd . '/config/autoload', 0775, true);
        mkdir($this->wd . '/module');

        $result = $this->controller->fsPermissionsAction();
        self::assertInstanceOf(ViewModel::class, $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        self::assertNotNull($fsPerms);
        self::assertTrue($fsPerms);
    }

    public function testReturnsFalseIfNeitherConfigNorModuleDirectoriesExistAndRootIsNotWritable(): void
    {
        if (! file_exists('/var/log') || ! is_dir('/var/log') || is_writable('/var/log')) {
            $this->markTestSkipped('Cannot test, as either /var/log does not exist or is writable');
        }

        chdir('/var/log');

        // Instantiating new controller, as constructor caches getcwd()
        $controller = new FsPermissionsController();
        $result     = $controller->fsPermissionsAction();
        self::assertInstanceOf(ViewModel::class, $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        self::assertNotNull($fsPerms);
        self::assertFalse($fsPerms);
    }

    public function testReturnsFalseIfConfigAndModuleDirectoriesExistButAreNotWritable(): void
    {
        $this->markTestSkipped(
            'Unable to determine how to test this case, as requires having a directory not owned by test runner'
        );
    }
}
