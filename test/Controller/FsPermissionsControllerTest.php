<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Controller;

use FilesystemIterator;
use Laminas\ApiTools\Admin\Controller\FsPermissionsController;
use PHPUnit_Framework_TestCase as TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FsPermissionsControllerTest extends TestCase
{
    public function setUp()
    {
        $this->pwd  = getcwd();
        $this->wd   = sys_get_temp_dir() . '/' . 'ag-admin-' . uniqid();
        mkdir($this->wd);
        chdir($this->wd);

        $this->controller     = new FsPermissionsController();
    }

    public function tearDown()
    {
        chdir($this->pwd);
        $this->removeDir($this->wd);
    }

    public function removeDir($directory)
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }
        rmdir($directory);
    }

    public function testReturnsTrueIfNeitherConfigNorModuleDirectoriesExistButRootIsWritable()
    {
        $result = $this->controller->fsPermissionsAction();
        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        $this->assertNotNull($fsPerms);
        $this->assertTrue($fsPerms);
    }

    public function testReturnsTrueIfConfigAndModuleDirectoriesExistAndAreWritable()
    {
        mkdir($this->wd . '/config/autoload', 0777, true);
        mkdir($this->wd . '/module');

        $result = $this->controller->fsPermissionsAction();
        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        $this->assertNotNull($fsPerms);
        $this->assertTrue($fsPerms);
    }

    public function testReturnsFalseIfNeitherConfigNorModuleDirectoriesExistAndRootIsNotWritable()
    {
        if (!file_exists('/var/log') || !is_dir('/var/log') || is_writable('/var/log')) {
            $this->markTestSkipped('Cannot test, as either /var/log does not exist or is writable');
        }

        chdir('/var/log');

        // Instantiating new controller, as constructor caches getcwd()
        $controller = new FsPermissionsController();
        $result = $controller->fsPermissionsAction();
        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        $this->assertNotNull($fsPerms);
        $this->assertFalse($fsPerms);
    }

    public function testReturnsFalseIfConfigAndModuleDirectoriesExistButAreNotWritable()
    {
        $this->markTestSkipped('Unable to determine how to test this case, as requires having a directory not owned by test runner');
    }
}
