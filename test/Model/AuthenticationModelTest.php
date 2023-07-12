<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception\InvalidArgumentException;
use Laminas\ApiTools\Admin\Model\AuthenticationEntity;
use Laminas\ApiTools\Admin\Model\AuthenticationModel;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\MvcAuth\Authentication\HttpAdapter;
use Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter;
use Laminas\ApiTools\OAuth2\Adapter\MongoAdapter;
use Laminas\ApiTools\OAuth2\Adapter\PdoAdapter;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use Laminas\Stdlib\ArrayUtils;
use MongoClient;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function class_exists;
use function count;
use function dirname;
use function extension_loaded;
use function file_exists;
use function file_put_contents;
use function getenv;
use function in_array;
use function is_array;
use function is_dir;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use function var_export;
use function version_compare;

class AuthenticationModelTest extends TestCase
{
    /** @var string */
    private $configPath;
    /** @var string */
    private $globalConfigPath;
    /** @var string */
    private $localConfigPath;
    /** @var ConfigWriter */
    private $configWriter;

    public function setUp(): void
    {
        $this->configPath       = sys_get_temp_dir() . '/api-tools-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->localConfigPath  = $this->configPath . '/local.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter = new ConfigWriter();
    }

    public function tearDown(): void
    {
        $this->removeConfigMocks();
    }

    public function createConfigMocks(): void
    {
        if (! is_dir($this->configPath)) {
            mkdir($this->configPath, 0775, true);
        }

        $contents = "<" . "?php\nreturn array();";
        file_put_contents($this->globalConfigPath, $contents);
        file_put_contents($this->localConfigPath, $contents);
    }

    public function removeConfigMocks(): void
    {
        if (file_exists($this->globalConfigPath)) {
            unlink($this->globalConfigPath);
        }
        if (file_exists($this->localConfigPath)) {
            unlink($this->localConfigPath);
        }
        if (is_dir($this->configPath)) {
            rmdir($this->configPath);
        }
        if (is_dir(dirname($this->configPath))) {
            rmdir(dirname($this->configPath));
        }
    }

    /**
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function createModelFromConfigArrays(array $global, array $local): AuthenticationModel
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $this->configWriter->toFile($this->localConfigPath, $local);
        $mergedConfig = ArrayUtils::merge($global, $local);
        $globalConfig = new ConfigResource($mergedConfig, $this->globalConfigPath, $this->configWriter);
        $localConfig  = new ConfigResource($mergedConfig, $this->localConfigPath, $this->configWriter);

        $moduleEntity = $this->getMockBuilder(ModuleEntity::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $moduleEntity->expects($this->any())
                     ->method('getName')
                     ->will($this->returnValue('Foo'));

        $moduleEntity->expects($this->any())
                     ->method('getVersions')
                     ->will($this->returnValue([1, 2]));

        $moduleModel = $this->getMockBuilder(ModuleModel::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $moduleModel->expects($this->any())
                    ->method('getModules')
                    ->will($this->returnValue(['Foo' => $moduleEntity]));

        return new AuthenticationModel($globalConfig, $localConfig, $moduleModel);
    }

    /** @param array<string, mixed> $config */
    public function assertAuthenticationConfigExists(string $key, array $config): void
    {
        self::assertArrayHasKey('api-tools-mvc-auth', $config);
        self::assertArrayHasKey('authentication', $config['api-tools-mvc-auth']);
        self::assertArrayHasKey($key, $config['api-tools-mvc-auth']['authentication']);
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $config
     */
    public function assertAuthenticationConfigEquals(string $key, array $expected, array $config): void
    {
        self::assertAuthenticationConfigExists($key, $config);
        $config = $config['api-tools-mvc-auth']['authentication'][$key];
        self::assertEquals($expected, $config);
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $config
     */
    public function assertAuthenticationConfigContains(string $authKey, array $expected, array $config): void
    {
        self::assertAuthenticationConfigExists($authKey, $config);
        $config = $config['api-tools-mvc-auth']['authentication'][$authKey];
        foreach ($expected as $key => $value) {
            self::assertArrayHasKey($key, $config);
            self::assertEquals($value, $config[$key]);
        }
    }

    public function testCreatesBothGlobalAndLocalConfigWhenNoneExistedPreviously(): void
    {
        $toCreate = [
            'accept_schemes' => ['basic'],
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        ];

        $model = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $global = include $this->globalConfigPath;
        self::assertAuthenticationConfigEquals('http', [
            'accept_schemes' => ['basic'],
            'realm'          => 'laminascon',
        ], $global);

        $local = include $this->localConfigPath;
        self::assertAuthenticationConfigEquals('http', [
            'htpasswd' => __DIR__ . '/htpasswd',
        ], $local);
    }

    public function testCanRetrieveAuthenticationConfig(): void
    {
        $globalSeedConfig = [
            'api-tools-mvc-auth' => [
                'authentication' => [
                    'http' => [
                        'accept_schemes' => ['basic'],
                        'realm'          => 'laminascon',
                    ],
                ],
            ],
        ];
        $localSeedConfig  = [
            'api-tools-mvc-auth' => [
                'authentication' => [
                    'http' => [
                        'htpasswd' => __DIR__ . '/htpasswd',
                    ],
                ],
            ],
        ];
        $model            = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $entity           = $model->fetch();
        self::assertInstanceOf(AuthenticationEntity::class, $entity);
        $expected = array_merge(
            ['type' => 'http_basic'],
            $globalSeedConfig['api-tools-mvc-auth']['authentication']['http'],
            $localSeedConfig['api-tools-mvc-auth']['authentication']['http']
        );
        self::assertEquals($expected, $entity->getArrayCopy());
    }

    public function testUpdatesGlobalAndLocalConfigWhenUpdating(): void
    {
        $toCreate = [
            'accept_schemes' => ['basic'],
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        ];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $newConfig = [
            'realm'    => 'api',
            'htpasswd' => sys_get_temp_dir() . '/htpasswd',
        ];
        $entity    = $model->update($newConfig);

        // Ensure the entity returned from the update is what we expect
        self::assertInstanceOf(AuthenticationEntity::class, $entity);
        $expected = array_merge(['type' => 'http_basic'], $toCreate, $newConfig);
        self::assertEquals($expected, $entity->getArrayCopy());

        // Ensure fetching the entity after an update will return what we expect
        $config = include $this->globalConfigPath;
        self::assertAuthenticationConfigEquals('http', [
            'accept_schemes' => ['basic'],
            'realm'          => 'api',
        ], $config);

        $config = include $this->localConfigPath;
        self::assertAuthenticationConfigEquals('http', ['htpasswd' => sys_get_temp_dir() . '/htpasswd'], $config);
    }

    public function testRemoveDeletesConfigurationFromBothLocalAndGlobalConfigFiles(): void
    {
        $toCreate = [
            'accept_schemes' => ['basic'],
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        ];
        $model    = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $model->remove();
        $global = include $this->globalConfigPath;
        self::assertArrayNotHasKey('http', $global['api-tools-mvc-auth']['authentication']);
        $local = include $this->localConfigPath;
        self::assertArrayNotHasKey('http', $local['api-tools-mvc-auth']['authentication']);
    }

    public function testCreatingOAuth2ConfigurationWritesToEachConfigFile(): void
    {
        $toCreate = [
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ];

        $model = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $global = include $this->globalConfigPath;
        self::assertArrayHasKey('router', $global);
        self::assertArrayHasKey('routes', $global['router']);
        self::assertArrayHasKey('oauth', $global['router']['routes']);
        self::assertArrayHasKey('options', $global['router']['routes']['oauth']);
        self::assertArrayHasKey('route', $global['router']['routes']['oauth']['options']);
        self::assertEquals(
            '/api/oauth',
            $global['router']['routes']['oauth']['options']['route'],
            var_export($global, true)
        );

        $local = include $this->localConfigPath;
        self::assertEquals([
            'storage' => PdoAdapter::class,
            'db'      => [
                'dsn_type' => 'PDO',
                'dsn'      => 'sqlite::memory:',
                'username' => 'me',
                'password' => 'too',
            ],
        ], $local['api-tools-oauth2']);
    }

    public function testCreatingOAuth2ConfigurationWritesToEachConfigFileForMongo(): void
    {
        if (
            ! (extension_loaded('mongo') || extension_loaded('mongodb'))
            || ! class_exists(MongoClient::class)
            || version_compare((string) MongoClient::VERSION, '1.4.1', '<')
        ) {
            $this->markTestSkipped('ext/mongo ^1.4.1 or ext/mongodb + alcaeus/mongo-php-adapter is not available');
        }

        $toCreate = [
            'dsn'         => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
            'database'    => 'apiToolsTest',
            'dsn_type'    => 'Mongo',
            'route_match' => '/api/oauth',
        ];

        $model = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $global = include $this->globalConfigPath;
        self::assertArrayHasKey('router', $global);
        self::assertArrayHasKey('routes', $global['router']);
        self::assertArrayHasKey('oauth', $global['router']['routes']);
        self::assertArrayHasKey('options', $global['router']['routes']['oauth']);
        self::assertArrayHasKey('route', $global['router']['routes']['oauth']['options']);
        self::assertEquals(
            '/api/oauth',
            $global['router']['routes']['oauth']['options']['route'],
            var_export($global, true)
        );

        $local = include $this->localConfigPath;
        self::assertEquals([
            'storage' => MongoAdapter::class,
            'mongo'   => [
                'dsn_type' => 'Mongo',
                'dsn'      => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
                'username' => null,
                'password' => null,
                'database' => 'apiToolsTest',
            ],
        ], $local['api-tools-oauth2']);
    }

    public function testRemovingOAuth2ConfigurationRemovesConfigurationFromEachFile(): void
    {
        $toCreate = [
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ];

        $model = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $model->remove();

        $global = include $this->globalConfigPath;
        self::assertArrayNotHasKey('oauth', $global['router']['routes']);
        self::assertFalse(isset($global['router']['routes']['oauth']));
        $local = include $this->localConfigPath;
        self::assertFalse(isset($local['router']['routes']['oauth']));
        self::assertArrayNotHasKey('db', $local['api-tools-oauth2']);
        self::assertArrayNotHasKey('storage', $local['api-tools-oauth2']);
    }

    /**
     * @group 172
     */
    public function testRemovingOAuth2MongoConfigurationRemovesConfigurationFromEachFile(): void
    {
        if (
            ! (extension_loaded('mongo') || extension_loaded('mongodb'))
            || ! class_exists(MongoClient::class)
            || version_compare((string) MongoClient::VERSION, '1.4.1', '<')
        ) {
            $this->markTestSkipped('ext/mongo ^1.4.1 or ext/mongodb + alcaeus/mongo-php-adapter is not available');
        }

        $toCreate = [
            'dsn_type'    => 'mongo',
            'dsn'         => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING') . '/api-tools',
            'route_match' => '/api/oauth',
        ];

        $model = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $model->remove();

        $global = include $this->globalConfigPath;
        self::assertArrayNotHasKey('oauth', $global['router']['routes']);
        self::assertFalse(isset($global['router']['routes']['oauth']));
        $local = include $this->localConfigPath;
        self::assertFalse(isset($local['router']['routes']['oauth']));
        self::assertArrayNotHasKey('mongo', $local['api-tools-oauth2']);
        self::assertArrayNotHasKey('storage', $local['api-tools-oauth2']);
    }

    /**
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToCreateOAuth2ConfigurationWithInvalidMongoDsnRaisesException(): void
    {
        if (
            ! (extension_loaded('mongo') || extension_loaded('mongodb'))
            || ! class_exists(MongoClient::class)
            || version_compare((string) MongoClient::VERSION, '1.4.1', '<')
        ) {
            $this->markTestSkipped('ext/mongo ^1.4.1 or ext/mongodb + alcaeus/mongo-php-adapter is not available');
        }

        $toCreate = [
            'dsn'         => 'mongodb:.300.300.300.300',
            'database'    => 'wrong',
            'route_match' => '/api/oauth',
            'dsn_type'    => 'Mongo',
        ];
        $model    = $this->createModelFromConfigArrays([], []);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DSN');
        $this->expectExceptionCode(422);
        $model->create($toCreate);
    }

    /**
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToCreateOAuth2ConfigurationWithInvalidDsnRaisesException(): void
    {
        $toCreate = [
            'dsn'         => 'sqlite:/tmp/' . uniqid() . '/.db',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ];
        $model    = $this->createModelFromConfigArrays([], []);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DSN');
        $this->expectExceptionCode(422);
        $model->create($toCreate);
    }

    /**
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToUpdateOAuth2ConfigurationWithInvalidDsnRaisesException(): void
    {
        $toCreate = [
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ];
        $model    = $this->createModelFromConfigArrays([], []);

        $model->create($toCreate);
        $newConfig = [
            'dsn' => 'sqlite:/tmp/' . uniqid() . '/.db',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('DSN');
        $this->expectExceptionCode(422);
        $model->update($newConfig);
    }

    /** @return array<string, mixed>[][] */
    public function getAuthAdapters(): array
    {
        return [
            [
                [ // global
                    'api-tools-mvc-auth' => [
                        'authentication' => [
                            'map' => [
                                'Status\V1' => 'test1',
                                'Status\V2' => 'test2',
                                'Foo'       => 'test3',
                                'Bar'       => 'test4',
                            ],
                        ],
                    ],
                    'router'             => [
                        'routes' => [
                            'oauth' => [
                                'type'    => 'regex',
                                'options' => [
                                    'regex' => '(?P<oauth>(/oauth_mongo|/oauth_pdo))',
                                    'spec'  => '%oauth%',
                                ],
                            ],
                        ],
                    ],
                ],
                [ // local
                    'api-tools-mvc-auth' => [
                        'authentication' => [
                            'adapters' => [
                                'test1' => [
                                    'adapter' => HttpAdapter::class,
                                    'options' => [
                                        'accept_schemes' => ['basic'],
                                        'realm'          => 'api',
                                        'htpasswd'       => 'data/htpasswd',
                                    ],
                                ],
                                'test2' => [
                                    'adapter' => HttpAdapter::class,
                                    'options' => [
                                        'accept_schemes' => ['digest'],
                                        'realm'          => 'api',
                                        'digest_domains' => 'domain.com',
                                        'nonce_timeout'  => 3600,
                                        'htdigest'       => 'data/htpasswd',
                                    ],
                                ],
                                'test3' => [
                                    'adapter' => OAuth2Adapter::class,
                                    'storage' => [
                                        'adapter'  => 'pdo',
                                        'route'    => '/oauth_pdo',
                                        'dsn'      => 'mysql:host=localhost;dbname=oauth2',
                                        'username' => 'test',
                                        'password' => 'test',
                                        'options'  => [
                                            1002 => 'SET NAMES utf8',
                                        ],
                                    ],
                                ],
                                'test4' => [
                                    'adapter' => OAuth2Adapter::class,
                                    'storage' => [
                                        'adapter'      => 'mongo',
                                        'route'        => '/oauth_mongo',
                                        'locator_name' => 'SomeServiceName',
                                        // phpcs:ignore Generic.Files.LineLength.TooLong
                                        'dsn'      => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
                                        'database' => 'oauth2',
                                        'options'  => [
                                            'username'         => 'username',
                                            'password'         => 'password',
                                            'connectTimeoutMS' => 500,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test fetch all authentication adapters
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testFetchAllAuthenticationAdapter(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $result = $model->fetchAllAuthenticationAdapter();
        self::assertTrue(is_array($result));
        self::assertEquals(4, count($result));
        self::assertEquals('test1', $result[0]['name']);
        self::assertEquals('test2', $result[1]['name']);
        self::assertEquals('test3', $result[2]['name']);
        self::assertEquals('test4', $result[3]['name']);
    }

    /**
     * Test fetch a specific authentication adapter
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testFetchAuthenticationAdapter(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $result = $model->fetchAuthenticationAdapter('test3');
        self::assertTrue(is_array($result));
        self::assertEquals('test3', $result['name']);
        self::assertEquals('oauth2', $result['type']);
        self::assertEquals(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test3']['storage']['adapter'],
            $result['oauth2_type']
        );
        self::assertEquals(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test3']['storage']['dsn'],
            $result['oauth2_dsn']
        );
        self::assertEquals(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test3']['storage']['route'],
            $result['oauth2_route']
        );
        self::assertEquals(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test3']['storage']['options'],
            $result['oauth2_options']
        );
    }

    /** @return array<string, mixed>[] */
    public function getDataForAuthAdapters(): array
    {
        return [
            [
                'name'     => 'test10',
                'type'     => 'basic',
                'realm'    => 'api',
                'htpasswd' => __DIR__ . '/TestAsset/htpasswd',
            ],
            [
                'name'           => 'test11',
                'type'           => 'digest',
                'realm'          => 'api',
                'digest_domains' => 'domain.com',
                'nonce_timeout'  => 3600,
                'htdigest'       => __DIR__ . '/TestAsset/htdigest',
            ],
            [
                'name'            => 'test12',
                'type'            => 'oauth2',
                'oauth2_type'     => 'pdo',
                'oauth2_dsn'      => 'sqlite:' . __DIR__ . '/TestAsset/db.sqlite',
                'oauth2_route'    => '/oauth12',
                'oauth2_username' => null,
                'oauth2_password' => null,
                'oauth2_options'  => [
                    'foo' => 'bar',
                ],
            ],
            [
                'name'                => 'test13',
                'type'                => 'oauth2',
                'oauth2_type'         => 'mongo',
                'oauth2_dsn'          => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
                'oauth2_database'     => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_DATABASE'),
                'oauth2_route'        => '/oauth13',
                'oauth2_locator_name' => null,
                'oauth2_options'      => [
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    /**
     * Test create an authentication adapter
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testCreateAuthenticationAdapter(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $data = $this->getDataForAuthAdapters();
        foreach ($data as $adapter) {
            if (
                isset($adapter['oauth2_type'])
                && 'mongo' === $adapter['oauth2_type']
                && ! extension_loaded('mongo')
            ) {
                // Cannot create a Mongo adapter on systems without the Mongo extension
                continue;
            }
            $result = $model->createAuthenticationAdapter($adapter);
            self::assertTrue(is_array($result));
            self::assertEquals($adapter, $result);
            if ('oauth2' === $result['type']) {
                $config = include $this->globalConfigPath;
                self::assertTrue(in_array($adapter['oauth2_route'], $model->fromOAuth2RegexToArray($config)));
            }
        }
    }

    /**
     * Test update an authentication adapter
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testUpdateAuthenticationAdapter(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $data            = $this->getDataForAuthAdapters();
        $data[2]['name'] = 'test1';
        $result          = $model->updateAuthenticationAdapter('test1', $data[2]);
        self::assertTrue(is_array($result));
        self::assertEquals($data[2], $result);
        $config = include $this->globalConfigPath;
        self::assertTrue(in_array($data[2]['oauth2_route'], $model->fromOAuth2RegexToArray($config)));
    }

    /**
     * Test remove an authentication adapter
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testRemoveAuthenticationAdapter(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        self::assertTrue($model->removeAuthenticationAdapter('test4'));
        $config = include $this->localConfigPath;
        self::assertTrue(! isset($config['api-tools-mvc-auth']['authentication']['adapters']['test4']));
        $config = include $this->globalConfigPath;
        self::assertTrue(! in_array(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test4']['storage']['route'],
            $model->fromOAuth2RegexToArray($config)
        ));
    }

    /**
     * Test get authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     */
    public function testGetAuthenticationMap(array $global): void
    {
        $model = $this->createModelFromConfigArrays($global, []);

        $result = $model->getAuthenticationMap('Status', 1);
        self::assertEquals($global['api-tools-mvc-auth']['authentication']['map']['Status\V1'], $result);
        $result = $model->getAuthenticationMap('Foo');
        self::assertEquals($global['api-tools-mvc-auth']['authentication']['map']['Foo'], $result);
        $result = $model->getAuthenticationMap('User', 1);
        self::assertFalse($result);
        $result = $model->getAuthenticationMap('Test');
        self::assertFalse($result);
    }

    /**
     * Test add authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testAddAuthenticationMap(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        self::assertTrue($model->saveAuthenticationMap('test1', 'User', 1));
        self::assertEquals('test1', $model->getAuthenticationMap('User', 1));
    }

    /**
     * Test add invalid authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testAddInvalidAuthenticationMap(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $this->expectException(InvalidArgumentException::class);
        $model->saveAuthenticationMap('test', 'Foo', 1);
    }

    /**
     * Test update authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testUpdateAuthenticationMap(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        self::assertTrue($model->saveAuthenticationMap('test4', 'Status', 1));
        self::assertEquals('test4', $model->getAuthenticationMap('Status', 1));
        self::assertTrue($model->saveAuthenticationMap('test1', 'Foo'));
        self::assertEquals('test1', $model->getAuthenticationMap('Foo'));
    }

    /**
     * Test remove authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @param array<string, mixed> $global
     * @param array<string, mixed> $local
     */
    public function testRemoveAuthenticationMap(array $global, array $local): void
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        self::assertTrue($model->removeAuthenticationMap('Status', 1));
        self::assertFalse($model->getAuthenticationMap('Status', 1));
        $config = include $this->globalConfigPath;
        self::assertTrue(! isset($config['api-tools-mvc-auth']['authentication']['map']['Status\V1']));
        self::assertTrue($model->removeAuthenticationMap('Foo'));
        self::assertFalse($model->getAuthenticationMap('Foo'));
        $config = include $this->globalConfigPath;
        self::assertTrue(! isset($config['api-tools-mvc-auth']['authentication']['map']['Foo']));
    }

    /** @return array<string, array<string, array<string, mixed>>> */
    public function getOldAuthenticationConfig(): array
    {
        return [
            'http_basic'   => [
                'api-tools-mvc-auth' => [
                    'authentication' => [
                        'http' => [
                            'accept_schemes' => ['basic'],
                            'realm'          => 'My Web Site',
                            'htpasswd'       => __DIR__ . '/TestAsset/htpasswd',
                        ],
                    ],
                ],
            ],
            'http_digest'  => [
                'api-tools-mvc-auth' => [
                    'authentication' => [
                        'http' => [
                            'accept_schemes' => ['digest'],
                            'realm'          => 'My Web Site',
                            'digest_domains' => 'domain.com',
                            'nonce_timeout'  => 3600,
                            'htdigest'       => __DIR__ . '/TestAsset/htdigest',
                        ],
                    ],
                ],
            ],
            'oauth2_pdo'   => [
                'api-tools-oauth2' => [
                    'storage' => PdoAdapter::class,
                    'db'      => [
                        'dsn_type' => 'PDO',
                        'dsn'      => 'sqlite:/' . __DIR__ . '/TestAsset/db.sqlite',
                        'username' => null,
                        'password' => null,
                    ],
                ],
            ],
            'oauth2_mongo' => [
                'api-tools-oauth2' => [
                    'storage' => MongoAdapter::class,
                    'mongo'   => [
                        'dsn_type'     => 'Mongo',
                        'dsn'          => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
                        'database'     => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_DATABASE'),
                        'locator_name' => 'MongoDB',
                    ],
                ],
            ],
        ];
    }

    /**
     * Test transform old authentication configuration in authentication per APIs
     * Since Laminas API Tools 1.1
     */
    public function testTransformAuthPerApis(): void
    {
        $global = [
            'router' => [
                'routes' => [
                    'oauth' => [
                        'options' => [
                            'route' => '/oauth',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($this->getOldAuthenticationConfig() as $name => $local) {
            $model = $this->createModelFromConfigArrays($global, $local);

            self::assertEquals($name, $model->transformAuthPerApis());

            // Old authentication is empty
            self::assertFalse($model->fetch());

            // New authentication adapter exists
            $result = $model->fetchAuthenticationAdapter($name);
            self::assertEquals($name, $result['name']);

            // Authentication map exists
            self::assertEquals($result['name'], $model->getAuthenticationMap('Foo', 1));
            self::assertEquals($result['name'], $model->getAuthenticationMap('Foo', 2));
        }
    }

    public function testCustomAuthAdapters(): void
    {
        $local = [
            'api-tools-mvc-auth' => [
                'authentication' => [
                    'adapters' => [
                        'custom1' => [
                            'adapter' => OAuth2Adapter::class,
                            'storage' => [
                                'storage' => 'MyAuth\OAuth2Adapter',
                                'route'   => '/oauth',
                            ],
                        ],
                        'custom2' => [
                            'adapter' => 'MyAuth\\CustomAuthAdapter',
                            'storage' => [
                                'storage' => 'MyAuth\OAuth2Adapter',
                                'route'   => '/oauth',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $model = $this->createModelFromConfigArrays([], $local);

        $result = $model->fetchAllAuthenticationAdapter();
        self::assertEquals('custom', $result[0]['oauth2_type']);
        self::assertEquals('custom', $result[1]['type']);
        self::assertEquals('/oauth', $result[1]['route']);
    }
}
