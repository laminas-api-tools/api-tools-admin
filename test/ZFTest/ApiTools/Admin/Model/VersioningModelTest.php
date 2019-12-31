<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Admin\Model\VersioningModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\PhpArray;
use PHPUnit_Framework_TestCase as TestCase;
use Version;

require_once __DIR__ . '/TestAsset/module/Version/Module.php';

class VersioningModelTest extends TestCase
{
    public function setUp()
    {
        $this->moduleConfigFile = __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        $this->setUpModuleConfig();

        $writer      = new PhpArray();
        $config      = include $this->moduleConfigFile;
        $resource    = new ConfigResource($config, $this->moduleConfigFile, $writer);
        $this->model = new VersioningModel($resource);
    }

    public function tearDown()
    {
        $this->removeModuleConfig();
        $this->removeDir(__DIR__ . "/TestAsset/module/Version/src/Version/V2");
    }

    public function removeModuleConfig()
    {
        if (file_exists($this->moduleConfigFile)) {
            unlink($this->moduleConfigFile);
        }
    }

    public function setUpModuleConfig()
    {
        $this->removeModuleConfig();
        copy($this->moduleConfigFile . '.dist', $this->moduleConfigFile);
    }

    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return boolean
     */
    public function removeDir($dir)
    {
        if (!file_exists($dir)) {
            return false;
        }
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

    public function testGetModuleVersions()
    {
        $versions = $this->model->getModuleVersions('Version', __DIR__ . '/TestAsset/module/Version/src/Version');
        $this->assertEquals(array(1), $versions);
    }

    public function testCreateVersion()
    {
        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        $this->assertTrue($result);
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest"));

        $config = include($this->moduleConfigFile);
        $this->assertArrayHasKey('router', $config);
        $this->assertEquals('Version\\V1\\Rest\Message\Controller', $config['router']['routes']['version.rest.message']['options']['defaults']['controller']);
        $this->assertEquals('Version\\V1\\Rest\Comment\Controller', $config['router']['routes']['version.rest.comment']['options']['defaults']['controller']);

        $this->assertArrayHasKey('api-tools-rest', $config);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\Controller', $config['api-tools-rest']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\Controller', $config['api-tools-rest'], var_export($config, 1));
        $this->assertEquals('Version\\V1\\Rest\\Message\\MessageResource', $config['api-tools-rest']['Version\\V1\\Rest\\Message\\Controller']['listener']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\MessageResource', $config['api-tools-rest']['Version\\V2\\Rest\\Message\\Controller']['listener']);
        $this->assertEquals('Version\\V1\\Rest\\Message\\MessageEntity', $config['api-tools-rest']['Version\\V1\\Rest\\Message\\Controller']['entity_class']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\MessageEntity', $config['api-tools-rest']['Version\\V2\\Rest\\Message\\Controller']['entity_class']);
        $this->assertEquals('Version\\V1\\Rest\\Message\\MessageCollection', $config['api-tools-rest']['Version\\V1\\Rest\\Message\\Controller']['collection_class']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\MessageCollection', $config['api-tools-rest']['Version\\V2\\Rest\\Message\\Controller']['collection_class']);
        $this->assertEquals('Version\\V1\\Rest\\Comment\\CommentResource', $config['api-tools-rest']['Version\\V1\\Rest\\Comment\\Controller']['listener']);
        $this->assertEquals('Version\\V2\\Rest\\Comment\\CommentResource', $config['api-tools-rest']['Version\\V2\\Rest\\Comment\\Controller']['listener']);
        $this->assertEquals('Version\\V1\\Rest\\Comment\\CommentEntity', $config['api-tools-rest']['Version\\V1\\Rest\\Comment\\Controller']['entity_class']);
        $this->assertEquals('Version\\V2\\Rest\\Comment\\CommentEntity', $config['api-tools-rest']['Version\\V2\\Rest\\Comment\\Controller']['entity_class']);
        $this->assertEquals('Version\\V1\\Rest\\Comment\\CommentCollection', $config['api-tools-rest']['Version\\V1\\Rest\\Comment\\Controller']['collection_class']);
        $this->assertEquals('Version\\V2\\Rest\\Comment\\CommentCollection', $config['api-tools-rest']['Version\\V2\\Rest\\Comment\\Controller']['collection_class']);

        $this->assertArrayHasKey('api-tools-hal', $config);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageEntity', $config['api-tools-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageEntity', $config['api-tools-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageCollection', $config['api-tools-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageCollection', $config['api-tools-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Comment\\CommentEntity', $config['api-tools-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Comment\\CommentEntity', $config['api-tools-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Comment\\CommentCollection', $config['api-tools-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Comment\\CommentCollection', $config['api-tools-hal']['metadata_map']);

        $this->assertArrayHasKey('api-tools', $config);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageResource', $config['api-tools']['db-connected']);
        $this->assertEquals('Version\\V1\\Rest\\Message\\Controller', $config['api-tools']['db-connected']['Version\\V1\\Rest\\Message\\MessageResource']['controller_service_name']);
        $this->assertEquals('Version\\V1\\Rest\\Message\\MessageResource\\Table', $config['api-tools']['db-connected']['Version\\V1\\Rest\\Message\\MessageResource']['table_service']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageResource', $config['api-tools']['db-connected']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\Controller', $config['api-tools']['db-connected']['Version\\V2\\Rest\\Message\\MessageResource']['controller_service_name']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\MessageResource\\Table', $config['api-tools']['db-connected']['Version\\V2\\Rest\\Message\\MessageResource']['table_service']);

        $this->assertArrayHasKey('service_manager', $config);
        $this->assertEquals('Version\V1\Rest\Comment\CommentModelFactory', $config['service_manager']['factories']['Version\V1\Rest\Comment\Model']);
        $this->assertEquals('Version\V1\Rest\Comment\CommentResourceFactory', $config['service_manager']['factories']['Version\V1\Rest\Comment\CommentResource']);
        $this->assertEquals('Version\V2\Rest\Comment\CommentModelFactory', $config['service_manager']['factories']['Version\V2\Rest\Comment\Model']);
        $this->assertEquals('Version\V2\Rest\Comment\CommentResourceFactory', $config['service_manager']['factories']['Version\V2\Rest\Comment\CommentResource']);

        $this->removeDir(__DIR__ . "/TestAsset/module/Version/src/Version/V2");
    }

    public function testCreateVersionRenamesNamespacesInCopiedClasses()
    {
        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc/Bar/BarController.php"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest/Foo/FooEntity.php"));

        $nsSep      = preg_quote('\\');
        $pattern1 = sprintf(
            '#Version%sV1%s#',
            $nsSep,
            $nsSep
        );
        $pattern2 = str_replace('1', '2', $pattern1);

        $controller = file_get_contents(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc/Bar/BarController.php");
        $this->assertNotRegExp($pattern1, $controller);
        $this->assertRegExp($pattern2, $controller);

        $entity = file_get_contents(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest/Foo/FooEntity.php");
        $this->assertNotRegExp($pattern1, $entity);
        $this->assertRegExp($pattern2, $entity);
    }
}