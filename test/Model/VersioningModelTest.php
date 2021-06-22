<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\VersioningModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\PhpArray;
use PHPUnit\Framework\TestCase;

use function var_export;

class VersioningModelTest extends TestCase
{
    /** @var VersioningModel */
    private $model;

    /** @var string */
    private $moduleConfigFile;

    /** @var string */
    private $moduleDocsConfigFile;

    public function setUp(): void
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

    public function tearDown(): void
    {
        $this->removeModuleConfig();
        $this->removeDir(__DIR__ . "/TestAsset/module/Version/src/Version/V2");
    }

    public function removeModuleConfig(): void
    {
        foreach ([$this->moduleConfigFile, $this->moduleDocsConfigFile] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function setUpModuleConfig(): void
    {
        $this->removeModuleConfig();
        foreach ([$this->moduleConfigFile, $this->moduleDocsConfigFile] as $file) {
            copy($file . '.dist', $file);
        }
    }

    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param string $dir
     * @return bool
     */
    public function removeDir(string $dir): bool
    {
        if (!file_exists($dir)) {
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

    public function testGetModuleVersions(): void
    {
        $versions = $this->model->getModuleVersions('Version', __DIR__ . '/TestAsset/module/Version/src/Version');
        self::assertEquals([1], $versions);
    }

    public function testCreateVersion(): void
    {
        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        self::assertTrue($result);
        self::assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2"));
        self::assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc"));
        self::assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest"));

        $config = include $this->moduleConfigFile;
        $this->assertArrayHasKey('router', $config);

        // phpcs:disable Generic.Files.LineLength.TooLong,WebimpressCodingStandard.Formatting.StringClassReference.Found
        self::assertEquals('Version\\V1\\Rest\Message\Controller', $config['router']['routes']['version.rest.message']['options']['defaults']['controller']);
        self::assertEquals('Version\\V1\\Rest\Comment\Controller', $config['router']['routes']['version.rest.comment']['options']['defaults']['controller']);

        self::assertArrayHasKey('api-tools-rest', $config);
        self::assertArrayHasKey('Version\\V1\\Rest\\Message\\Controller', $config['api-tools-rest']);
        self::assertArrayHasKey('Version\\V2\\Rest\\Message\\Controller', $config['api-tools-rest'], var_export($config, true));

        self::assertEquals('Version\\V1\\Rest\\Message\\MessageResource', $config['api-tools-rest']['Version\\V1\\Rest\\Message\\Controller']['listener']);
        self::assertEquals('Version\\V2\\Rest\\Message\\MessageResource', $config['api-tools-rest']['Version\\V2\\Rest\\Message\\Controller']['listener']);
        self::assertEquals('Version\\V1\\Rest\\Message\\MessageEntity', $config['api-tools-rest']['Version\\V1\\Rest\\Message\\Controller']['entity_class']);
        self::assertEquals('Version\\V2\\Rest\\Message\\MessageEntity', $config['api-tools-rest']['Version\\V2\\Rest\\Message\\Controller']['entity_class']);
        self::assertEquals('Version\\V1\\Rest\\Message\\MessageCollection', $config['api-tools-rest']['Version\\V1\\Rest\\Message\\Controller']['collection_class']);
        self::assertEquals('Version\\V2\\Rest\\Message\\MessageCollection', $config['api-tools-rest']['Version\\V2\\Rest\\Message\\Controller']['collection_class']);
        self::assertEquals('Version\\V1\\Rest\\Comment\\CommentResource', $config['api-tools-rest']['Version\\V1\\Rest\\Comment\\Controller']['listener']);
        self::assertEquals('Version\\V2\\Rest\\Comment\\CommentResource', $config['api-tools-rest']['Version\\V2\\Rest\\Comment\\Controller']['listener']);
        self::assertEquals('Version\\V1\\Rest\\Comment\\CommentEntity', $config['api-tools-rest']['Version\\V1\\Rest\\Comment\\Controller']['entity_class']);
        self::assertEquals('Version\\V2\\Rest\\Comment\\CommentEntity', $config['api-tools-rest']['Version\\V2\\Rest\\Comment\\Controller']['entity_class']);
        self::assertEquals('Version\\V1\\Rest\\Comment\\CommentCollection', $config['api-tools-rest']['Version\\V1\\Rest\\Comment\\Controller']['collection_class']);
        self::assertEquals('Version\\V2\\Rest\\Comment\\CommentCollection', $config['api-tools-rest']['Version\\V2\\Rest\\Comment\\Controller']['collection_class']);

        self::assertArrayHasKey('api-tools-hal', $config);
        self::assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageEntity', $config['api-tools-hal']['metadata_map']);
        self::assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageEntity', $config['api-tools-hal']['metadata_map']);
        self::assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageCollection', $config['api-tools-hal']['metadata_map']);
        self::assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageCollection', $config['api-tools-hal']['metadata_map']);
        self::assertArrayHasKey('Version\\V1\\Rest\\Comment\\CommentEntity', $config['api-tools-hal']['metadata_map']);
        self::assertArrayHasKey('Version\\V2\\Rest\\Comment\\CommentEntity', $config['api-tools-hal']['metadata_map']);
        self::assertArrayHasKey('Version\\V1\\Rest\\Comment\\CommentCollection', $config['api-tools-hal']['metadata_map']);
        self::assertArrayHasKey('Version\\V2\\Rest\\Comment\\CommentCollection', $config['api-tools-hal']['metadata_map']);

        self::assertArrayHasKey('api-tools', $config);
        self::assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageResource', $config['api-tools']['db-connected']);
        self::assertEquals('Version\\V1\\Rest\\Message\\Controller', $config['api-tools']['db-connected']['Version\\V1\\Rest\\Message\\MessageResource']['controller_service_name']);
        self::assertEquals('Version\\V1\\Rest\\Message\\MessageResource\\Table', $config['api-tools']['db-connected']['Version\\V1\\Rest\\Message\\MessageResource']['table_service']);
        self::assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageResource', $config['api-tools']['db-connected']);
        self::assertEquals('Version\\V2\\Rest\\Message\\Controller', $config['api-tools']['db-connected']['Version\\V2\\Rest\\Message\\MessageResource']['controller_service_name']);
        self::assertEquals('Version\\V2\\Rest\\Message\\MessageResource\\Table', $config['api-tools']['db-connected']['Version\\V2\\Rest\\Message\\MessageResource']['table_service']);

        self::assertArrayHasKey('service_manager', $config);
        self::assertEquals('Version\V1\Rest\Comment\CommentModelFactory', $config['service_manager']['factories']['Version\V1\Rest\Comment\Model']);
        self::assertEquals('Version\V1\Rest\Comment\CommentResourceFactory', $config['service_manager']['factories']['Version\V1\Rest\Comment\CommentResource']);
        self::assertEquals('Version\V2\Rest\Comment\CommentModelFactory', $config['service_manager']['factories']['Version\V2\Rest\Comment\Model']);
        self::assertEquals('Version\V2\Rest\Comment\CommentResourceFactory', $config['service_manager']['factories']['Version\V2\Rest\Comment\CommentResource']);

        self::assertArrayHasKey('controllers', $config);
        self::assertEquals('Version\V1\Rpc\Ping\PingController', $config['controllers']['invokables']['Version\V1\Rpc\Ping\Controller']);
        self::assertEquals('Version\V2\Rpc\Ping\PingController', $config['controllers']['invokables']['Version\V2\Rpc\Ping\Controller']);
        // phpcs:enable

        $this->removeDir(__DIR__ . "/TestAsset/module/Version/src/Version/V2");
    }

    public function testCreateVersionRenamesNamespacesInCopiedClasses(): void
    {
        $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');
        self::assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc/Bar/BarController.php"));
        self::assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest/Foo/FooEntity.php"));

        $nsSep    = preg_quote('\\');
        $pattern1 = sprintf(
            '#Version%sV1%s#',
            $nsSep,
            $nsSep
        );
        $pattern2 = str_replace('1', '2', $pattern1);

        $controller = file_get_contents(
            __DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc/Bar/BarController.php"
        );
        self::assertDoesNotMatchRegularExpression($pattern1, $controller);
        self::assertMatchesRegularExpression($pattern2, $controller);

        $entity = file_get_contents(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest/Foo/FooEntity.php");
        self::assertDoesNotMatchRegularExpression($pattern1, $entity);
        self::assertMatchesRegularExpression($pattern2, $entity);
    }

    public function testCreateNewVersionClonesAuthorizationConfigurationForNewVersion(): void
    {
        $originalConfig = include __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        self::assertArrayHasKey('api-tools-mvc-auth', $originalConfig);
        self::assertArrayHasKey('authorization', $originalConfig['api-tools-mvc-auth']);
        self::assertEquals(4, count($originalConfig['api-tools-mvc-auth']['authorization']));
        $originalAuthorization = $originalConfig['api-tools-mvc-auth']['authorization'];

        $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        $updatedConfig = include __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        self::assertArrayHasKey('api-tools-mvc-auth', $updatedConfig);
        self::assertArrayHasKey('authorization', $updatedConfig['api-tools-mvc-auth']);

        $updatedAuthorization = $updatedConfig['api-tools-mvc-auth']['authorization'];

        // loop through all services, ensure for any V1 versions, we also have V2 variants
        foreach (array_keys($originalAuthorization) as $serviceName) {
            // Should have the old configuration
            self::assertArrayHasKey($serviceName, $updatedAuthorization);
            self::assertEquals($originalAuthorization[$serviceName], $updatedAuthorization[$serviceName]);

            // AND the new configuration
            $newServiceName = str_replace('V1', 'V2', $serviceName);
            self::assertArrayHasKey($newServiceName, $updatedAuthorization);
            self::assertEquals($originalAuthorization[$serviceName], $updatedAuthorization[$newServiceName]);
        }
    }

    public function testCreateNewVersionClonesValidationConfigurationForNewVersion(): void
    {
        $originalConfig = include __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        self::assertArrayHasKey('api-tools-content-validation', $originalConfig);
        self::assertArrayHasKey('Version\V1\Rest\Message\Controller', $originalConfig['api-tools-content-validation']);
        self::assertArrayHasKey(
            'input_filter',
            $originalConfig['api-tools-content-validation']['Version\V1\Rest\Message\Controller']
        );
        self::assertArrayHasKey('input_filter_specs', $originalConfig);
        self::assertArrayHasKey('Version\V1\Rest\Message\Validator', $originalConfig['input_filter_specs']);

        $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        $updatedConfig = include __DIR__ . '/TestAsset/module/Version/config/module.config.php';

        self::assertArrayHasKey('api-tools-content-validation', $updatedConfig);
        self::assertArrayHasKey('Version\V1\Rest\Message\Controller', $updatedConfig['api-tools-content-validation']);
        self::assertArrayHasKey(
            'input_filter',
            $updatedConfig['api-tools-content-validation']['Version\V1\Rest\Message\Controller']
        );
        self::assertEquals(
            'Version\V1\Rest\Message\Validator',
            $updatedConfig['api-tools-content-validation']['Version\V1\Rest\Message\Controller']['input_filter']
        );

        self::assertArrayHasKey('Version\V2\Rest\Message\Controller', $updatedConfig['api-tools-content-validation']);
        self::assertArrayHasKey(
            'input_filter',
            $updatedConfig['api-tools-content-validation']['Version\V2\Rest\Message\Controller']
        );
        self::assertEquals(
            'Version\V2\Rest\Message\Validator',
            $updatedConfig['api-tools-content-validation']['Version\V2\Rest\Message\Controller']['input_filter']
        );

        self::assertArrayHasKey('input_filter_specs', $updatedConfig);
        self::assertArrayHasKey('Version\V1\Rest\Message\Validator', $updatedConfig['input_filter_specs']);
        self::assertArrayHasKey('Version\V2\Rest\Message\Validator', $updatedConfig['input_filter_specs']);
        self::assertEquals(
            $updatedConfig['input_filter_specs']['Version\V1\Rest\Message\Validator'],
            $updatedConfig['input_filter_specs']['Version\V2\Rest\Message\Validator']
        );
    }

    public function testSettingTheApiDefaultVersion(): void
    {
        $config = include $this->moduleConfigFile;
        self::assertSame(1, $config['api-tools-versioning']['default_version']);

        self::assertTrue($this->model->setDefaultVersion(1337));

        $newConfig = include $this->moduleConfigFile;
        self::assertSame(1337, $newConfig['api-tools-versioning']['default_version']);
    }

    public function testCreateNewVersionClonesDocumentationForNewVersion(): void
    {
        $docsConfig = include $this->moduleDocsConfigFile;
        self::assertArrayHasKey('Version\V1\Rest\Message\Controller', $docsConfig);
        self::assertArrayHasKey('Version\V1\Rest\Comment\Controller', $docsConfig);
        self::assertTrue(isset($docsConfig['Version\V1\Rest\Message\Controller']['collection']['GET']['response']));
        self::assertTrue(isset($docsConfig['Version\V1\Rest\Message\Controller']['entity']['GET']['response']));
        self::assertTrue(isset($docsConfig['Version\V1\Rest\Comment\Controller']['collection']['GET']['response']));
        self::assertTrue(isset($docsConfig['Version\V1\Rest\Comment\Controller']['entity']['GET']['response']));

        $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset/module/Version/src/Version');

        $newDocsConfig = include $this->moduleDocsConfigFile;
        self::assertArrayHasKey('Version\V2\Rest\Message\Controller', $newDocsConfig);
        self::assertArrayHasKey('Version\V2\Rest\Comment\Controller', $newDocsConfig);
        self::assertEquals(
            $docsConfig['Version\V1\Rest\Message\Controller'],
            $newDocsConfig['Version\V1\Rest\Message\Controller']
        );
        self::assertEquals(
            $docsConfig['Version\V1\Rest\Message\Controller'],
            $newDocsConfig['Version\V2\Rest\Message\Controller']
        );
        self::assertEquals(
            $docsConfig['Version\V1\Rest\Comment\Controller'],
            $newDocsConfig['Version\V1\Rest\Comment\Controller']
        );
        self::assertEquals(
            $docsConfig['Version\V1\Rest\Comment\Controller'],
            $newDocsConfig['Version\V2\Rest\Comment\Controller']
        );
    }
}
