<?php

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\VersioningModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\PhpArray;
use PHPUnit\Framework\TestCase;

class VersioningModelTest extends TestCase
{
    public function setUp()
    {
        $this->moduleConfigFile     = __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        $this->moduleDocsConfigFile = __DIR__ . '/TestAsset/module/Version/config/documentation.config.php';
        $this->setUpModuleConfig();

        $writer      = new PhpArray();
        $config      = include $this->moduleConfigFile;
        $docs        = include $this->moduleDocsConfigFile;
        $resource    = new ConfigResource($config, $this->moduleConfigFile, $writer);
        $docResource = new ConfigResource($docs, $this->moduleDocsConfigFile, $writer);
        $this->model = new VersioningModel($resource, $docResource);
    }

    public function tearDown()
    {
        $this->removeModuleConfig();
        $this->removeDir(__DIR__ . "/TestAsset/module/Version/src/Version/V2");
    }

    public function removeModuleConfig()
    {
        foreach ([$this->moduleConfigFile, $this->moduleDocsConfigFile] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function setUpModuleConfig()
    {
        $this->removeModuleConfig();
        foreach ([$this->moduleConfigFile, $this->moduleDocsConfigFile] as $file) {
            copy($file . '.dist', $file);
        }
    }

    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return bool
     */
    public function removeDir($dir)
    {
        if (! file_exists($dir)) {
            return false;
        }
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

    public function testGetModuleVersions()
    {
        $versions = $this->model->getModuleVersions('Version', __DIR__ . '/TestAsset/module/Version/src/Version');
        $this->assertEquals([1], $versions);
    }

    public function testCreateVersion()
    {
        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        $this->assertTrue($result);
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest"));

        $config = include $this->moduleConfigFile;
        $this->assertArrayHasKey('router', $config);
        // @codingStandardsIgnoreStart
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
        // @codingStandardsIgnoreEnd

        $this->assertArrayHasKey('api-tools', $config);
        $this->assertArrayHasKey(
            'Version\\V1\\Rest\\Message\\MessageResource',
            $config['api-tools']['db-connected']
        );
        // @codingStandardsIgnoreStart
        $this->assertEquals(
            'Version\\V1\\Rest\\Message\\Controller',
            $config['api-tools']['db-connected']['Version\\V1\\Rest\\Message\\MessageResource']['controller_service_name']
        );
        $this->assertEquals(
            'Version\\V1\\Rest\\Message\\MessageResource\\Table',
            $config['api-tools']['db-connected']['Version\\V1\\Rest\\Message\\MessageResource']['table_service']
        );
        // @codingStandardsIgnoreEnd
        $this->assertArrayHasKey(
            'Version\\V2\\Rest\\Message\\MessageResource',
            $config['api-tools']['db-connected']
        );
        // @codingStandardsIgnoreStart
        $this->assertEquals(
            'Version\\V2\\Rest\\Message\\Controller',
            $config['api-tools']['db-connected']['Version\\V2\\Rest\\Message\\MessageResource']['controller_service_name']
        );
        // @codingStandardsIgnoreEnd
        $this->assertEquals(
            'Version\\V2\\Rest\\Message\\MessageResource\\Table',
            $config['api-tools']['db-connected']['Version\\V2\\Rest\\Message\\MessageResource']['table_service']
        );

        $this->assertArrayHasKey('service_manager', $config);
        $this->assertEquals(
            'Version\V1\Rest\Comment\CommentModelFactory',
            $config['service_manager']['factories']['Version\V1\Rest\Comment\Model']
        );
        $this->assertEquals(
            'Version\V1\Rest\Comment\CommentResourceFactory',
            $config['service_manager']['factories']['Version\V1\Rest\Comment\CommentResource']
        );
        $this->assertEquals(
            'Version\V2\Rest\Comment\CommentModelFactory',
            $config['service_manager']['factories']['Version\V2\Rest\Comment\Model']
        );
        $this->assertEquals(
            'Version\V2\Rest\Comment\CommentResourceFactory',
            $config['service_manager']['factories']['Version\V2\Rest\Comment\CommentResource']
        );

        $this->assertArrayHasKey('controllers', $config);
        $this->assertEquals(
            'Version\V1\Rpc\Ping\PingController',
            $config['controllers']['invokables']['Version\V1\Rpc\Ping\Controller']
        );
        $this->assertEquals(
            'Version\V2\Rpc\Ping\PingController',
            $config['controllers']['invokables']['Version\V2\Rpc\Ping\Controller']
        );

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

        $controller = file_get_contents(
            __DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc/Bar/BarController.php"
        );
        $this->assertNotRegExp($pattern1, $controller);
        $this->assertRegExp($pattern2, $controller);

        $entity = file_get_contents(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest/Foo/FooEntity.php");
        $this->assertNotRegExp($pattern1, $entity);
        $this->assertRegExp($pattern2, $entity);
    }

    public function testCreateNewVersionClonesAuthorizationConfigurationForNewVersion()
    {
        $originalConfig = include __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        $this->assertArrayHasKey('api-tools-mvc-auth', $originalConfig);
        $this->assertArrayHasKey('authorization', $originalConfig['api-tools-mvc-auth']);
        $this->assertEquals(4, count($originalConfig['api-tools-mvc-auth']['authorization']));
        $originalAuthorization = $originalConfig['api-tools-mvc-auth']['authorization'];

        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        $updatedConfig = include __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        $this->assertArrayHasKey('api-tools-mvc-auth', $updatedConfig);
        $this->assertArrayHasKey('authorization', $updatedConfig['api-tools-mvc-auth']);

        $updatedAuthorization = $updatedConfig['api-tools-mvc-auth']['authorization'];

        // loop through all services, ensure for any V1 versions, we also have V2 variants
        foreach (array_keys($originalAuthorization) as $serviceName) {
            // Should have the old configuration
            $this->assertArrayHasKey($serviceName, $updatedAuthorization);
            $this->assertEquals($originalAuthorization[$serviceName], $updatedAuthorization[$serviceName]);

            // AND the new configuration
            $newServiceName = str_replace('V1', 'V2', $serviceName);
            $this->assertArrayHasKey($newServiceName, $updatedAuthorization);
            $this->assertEquals($originalAuthorization[$serviceName], $updatedAuthorization[$newServiceName]);
        }
    }

    public function testCreateNewVersionClonesValidationConfigurationForNewVersion()
    {
        $originalConfig = include __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        $this->assertArrayHasKey('api-tools-content-validation', $originalConfig);
        $this->assertArrayHasKey('Version\V1\Rest\Message\Controller', $originalConfig['api-tools-content-validation']);
        $this->assertArrayHasKey(
            'input_filter',
            $originalConfig['api-tools-content-validation']['Version\V1\Rest\Message\Controller']
        );
        $this->assertArrayHasKey('input_filter_specs', $originalConfig);
        $this->assertArrayHasKey('Version\V1\Rest\Message\Validator', $originalConfig['input_filter_specs']);

        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        $updatedConfig = include __DIR__ . '/TestAsset/module/Version/config/module.config.php';

        $this->assertArrayHasKey('api-tools-content-validation', $updatedConfig);
        $this->assertArrayHasKey('Version\V1\Rest\Message\Controller', $updatedConfig['api-tools-content-validation']);
        $this->assertArrayHasKey(
            'input_filter',
            $updatedConfig['api-tools-content-validation']['Version\V1\Rest\Message\Controller']
        );
        $this->assertEquals(
            'Version\V1\Rest\Message\Validator',
            $updatedConfig['api-tools-content-validation']['Version\V1\Rest\Message\Controller']['input_filter']
        );

        $this->assertArrayHasKey('Version\V2\Rest\Message\Controller', $updatedConfig['api-tools-content-validation']);
        $this->assertArrayHasKey(
            'input_filter',
            $updatedConfig['api-tools-content-validation']['Version\V2\Rest\Message\Controller']
        );
        $this->assertEquals(
            'Version\V2\Rest\Message\Validator',
            $updatedConfig['api-tools-content-validation']['Version\V2\Rest\Message\Controller']['input_filter']
        );

        $this->assertArrayHasKey('input_filter_specs', $updatedConfig);
        $this->assertArrayHasKey('Version\V1\Rest\Message\Validator', $updatedConfig['input_filter_specs']);
        $this->assertArrayHasKey('Version\V2\Rest\Message\Validator', $updatedConfig['input_filter_specs']);
        $this->assertEquals(
            $updatedConfig['input_filter_specs']['Version\V1\Rest\Message\Validator'],
            $updatedConfig['input_filter_specs']['Version\V2\Rest\Message\Validator']
        );
    }

    public function testSettingTheApiDefaultVersion()
    {
        $config = include $this->moduleConfigFile;
        $this->assertSame(1, $config['api-tools-versioning']['default_version']);

        $this->assertTrue($this->model->setDefaultVersion(1337));

        $newConfig = include $this->moduleConfigFile;
        $this->assertSame(1337, $newConfig['api-tools-versioning']['default_version']);
    }

    public function testCreateNewVersionClonesDocumentationForNewVersion()
    {
        $docsConfig = include $this->moduleDocsConfigFile;
        $this->assertArrayHasKey('Version\V1\Rest\Message\Controller', $docsConfig);
        $this->assertArrayHasKey('Version\V1\Rest\Comment\Controller', $docsConfig);
        $this->assertTrue(isset($docsConfig['Version\V1\Rest\Message\Controller']['collection']['GET']['response']));
        $this->assertTrue(isset($docsConfig['Version\V1\Rest\Message\Controller']['entity']['GET']['response']));
        $this->assertTrue(isset($docsConfig['Version\V1\Rest\Comment\Controller']['collection']['GET']['response']));
        $this->assertTrue(isset($docsConfig['Version\V1\Rest\Comment\Controller']['entity']['GET']['response']));

        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        $newDocsConfig = include $this->moduleDocsConfigFile;
        $this->assertArrayHasKey('Version\V2\Rest\Message\Controller', $newDocsConfig);
        $this->assertArrayHasKey('Version\V2\Rest\Comment\Controller', $newDocsConfig);
        $this->assertEquals(
            $docsConfig['Version\V1\Rest\Message\Controller'],
            $newDocsConfig['Version\V1\Rest\Message\Controller']
        );
        $this->assertEquals(
            $docsConfig['Version\V1\Rest\Message\Controller'],
            $newDocsConfig['Version\V2\Rest\Message\Controller']
        );
        $this->assertEquals(
            $docsConfig['Version\V1\Rest\Comment\Controller'],
            $newDocsConfig['Version\V1\Rest\Comment\Controller']
        );
        $this->assertEquals(
            $docsConfig['Version\V1\Rest\Comment\Controller'],
            $newDocsConfig['Version\V2\Rest\Comment\Controller']
        );
    }
}
