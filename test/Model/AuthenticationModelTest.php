<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\AuthenticationModel;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit_Framework_TestCase as TestCase;

class AuthenticationModelTest extends TestCase
{
    public function setUp()
    {
        $this->configPath       = sys_get_temp_dir() . '/api-tools-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->localConfigPath  = $this->configPath . '/local.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter     = new ConfigWriter();
    }

    public function tearDown()
    {
        $this->removeConfigMocks();
    }

    public function createConfigMocks()
    {
        if (!is_dir($this->configPath)) {
            mkdir($this->configPath, 0775, true);
        }

        $contents = "<" . "?php\nreturn array();";
        file_put_contents($this->globalConfigPath, $contents);
        file_put_contents($this->localConfigPath, $contents);
    }

    public function removeConfigMocks()
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

    public function createModelFromConfigArrays(array $global, array $local)
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $this->configWriter->toFile($this->localConfigPath, $local);
        $mergedConfig = ArrayUtils::merge($global, $local);
        $globalConfig = new ConfigResource($mergedConfig, $this->globalConfigPath, $this->configWriter);
        $localConfig  = new ConfigResource($mergedConfig, $this->localConfigPath, $this->configWriter);


        $moduleEntity = $this->getMockBuilder('Laminas\ApiTools\Admin\Model\ModuleEntity')
                            ->disableOriginalConstructor()
                            ->getMock();

        $moduleEntity->expects($this->any())
                     ->method('getName')
                     ->will($this->returnValue('Foo'));

        $moduleEntity->expects($this->any())
                     ->method('getVersions')
                     ->will($this->returnValue([1,2]));

        $moduleModel = $this->getMockBuilder('Laminas\ApiTools\Admin\Model\ModuleModel')
                            ->disableOriginalConstructor()
                            ->getMock();

        $moduleModel->expects($this->any())
                    ->method('getModules')
                    ->will($this->returnValue(['Foo' => $moduleEntity]));

        return new AuthenticationModel($globalConfig, $localConfig, $moduleModel);
    }

    public function assertAuthenticationConfigExists($key, array $config)
    {
        $this->assertArrayHasKey('api-tools-mvc-auth', $config);
        $this->assertArrayHasKey('authentication', $config['api-tools-mvc-auth']);
        $this->assertArrayHasKey($key, $config['api-tools-mvc-auth']['authentication']);
    }

    public function assertAuthenticationConfigEquals($key, array $expected, array $config)
    {
        $this->assertAuthenticationConfigExists($key, $config);
        $config = $config['api-tools-mvc-auth']['authentication'][$key];
        $this->assertEquals($expected, $config);
    }

    public function assertAuthenticationConfigContains($authKey, array $expected, array $config)
    {
        $this->assertAuthenticationConfigExists($authKey, $config);
        $config = $config['api-tools-mvc-auth']['authentication'][$authKey];
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $config);
            $this->assertEquals($value, $config[$key]);
        }
    }

    public function testCreatesBothGlobalAndLocalConfigWhenNoneExistedPreviously()
    {
        $toCreate = [
            'accept_schemes' => ['basic'],
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        ];

        $model    = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $global = include $this->globalConfigPath;
        $this->assertAuthenticationConfigEquals('http', [
            'accept_schemes' => ['basic'],
            'realm'          => 'laminascon',
        ], $global);

        $local  = include $this->localConfigPath;
        $this->assertAuthenticationConfigEquals('http', [
            'htpasswd'       => __DIR__ . '/htpasswd',
        ], $local);
    }

    public function testCanRetrieveAuthenticationConfig()
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
        $localSeedConfig = [
            'api-tools-mvc-auth' => [
                'authentication' => [
                    'http' => [
                        'htpasswd' => __DIR__ . '/htpasswd',
                    ],
                ],
            ],
        ];
        $model  = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $entity = $model->fetch();
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\AuthenticationEntity', $entity);
        $expected = array_merge(
            ['type' => 'http_basic'],
            $globalSeedConfig['api-tools-mvc-auth']['authentication']['http'],
            $localSeedConfig['api-tools-mvc-auth']['authentication']['http']
        );
        $this->assertEquals($expected, $entity->getArrayCopy());
    }

    public function testUpdatesGlobalAndLocalConfigWhenUpdating()
    {
        $toCreate = [
            'accept_schemes' => ['basic'],
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        ];
        $model = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $newConfig = [
            'realm'    => 'api',
            'htpasswd' => sys_get_temp_dir() . '/htpasswd',
        ];
        $entity = $model->update($newConfig);

        // Ensure the entity returned from the update is what we expect
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\AuthenticationEntity', $entity);
        $expected = array_merge(['type' => 'http_basic'], $toCreate, $newConfig);
        $this->assertEquals($expected, $entity->getArrayCopy());

        // Ensure fetching the entity after an update will return what we expect
        $config = include $this->globalConfigPath;
        $this->assertAuthenticationConfigEquals('http', [
            'accept_schemes' => ['basic'],
            'realm'          => 'api',
        ], $config);

        $config = include $this->localConfigPath;
        $this->assertAuthenticationConfigEquals('http', ['htpasswd' => sys_get_temp_dir() . '/htpasswd'], $config);
    }

    public function testRemoveDeletesConfigurationFromBothLocalAndGlobalConfigFiles()
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
        $this->assertArrayNotHasKey('http', $global['api-tools-mvc-auth']['authentication']);
        $local = include $this->localConfigPath;
        $this->assertArrayNotHasKey('http', $local['api-tools-mvc-auth']['authentication']);
    }

    public function testCreatingOAuth2ConfigurationWritesToEachConfigFile()
    {
        $toCreate = [
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ];

        $model    = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $global = include $this->globalConfigPath;
        $this->assertArrayHasKey('router', $global);
        $this->assertArrayHasKey('routes', $global['router']);
        $this->assertArrayHasKey('oauth', $global['router']['routes']);
        $this->assertArrayHasKey('options', $global['router']['routes']['oauth']);
        $this->assertArrayHasKey('route', $global['router']['routes']['oauth']['options']);
        $this->assertEquals(
            '/api/oauth',
            $global['router']['routes']['oauth']['options']['route'],
            var_export($global, 1)
        );

        $local  = include $this->localConfigPath;
        $this->assertEquals([
            'storage' => 'Laminas\ApiTools\OAuth2\Adapter\PdoAdapter',
            'db' => [
                'dsn_type'    => 'PDO',
                'dsn'         => 'sqlite::memory:',
                'username'    => 'me',
                'password'    => 'too',
            ],
        ], $local['api-tools-oauth2']);
    }

    public function testCreatingOAuth2ConfigurationWritesToEachConfigFileForMongo()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('mongo extension must be loaded to run this test');
        }

        $toCreate = [
            'dsn'         => 'mongodb://localhost:27017',
            'database'    => 'apiToolsTest',
            'dsn_type'    => 'Mongo',
            'route_match' => '/api/oauth',
        ];

        $model    = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $global = include $this->globalConfigPath;
        $this->assertArrayHasKey('router', $global);
        $this->assertArrayHasKey('routes', $global['router']);
        $this->assertArrayHasKey('oauth', $global['router']['routes']);
        $this->assertArrayHasKey('options', $global['router']['routes']['oauth']);
        $this->assertArrayHasKey('route', $global['router']['routes']['oauth']['options']);
        $this->assertEquals(
            '/api/oauth',
            $global['router']['routes']['oauth']['options']['route'],
            var_export($global, 1)
        );

        $local  = include $this->localConfigPath;
        $this->assertEquals([
            'storage' => 'Laminas\ApiTools\OAuth2\Adapter\MongoAdapter',
            'mongo' => [
                'dsn_type'    => 'Mongo',
                'dsn'         => 'mongodb://localhost:27017',
                'username'    => null,
                'password'    => null,
                'database'    => 'apiToolsTest',
            ],
        ], $local['api-tools-oauth2']);
    }

    public function testRemovingOAuth2ConfigurationRemovesConfigurationFromEachFile()
    {
        $toCreate = [
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ];

        $model    = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $model->remove();

        $global = include $this->globalConfigPath;
        $this->assertArrayNotHasKey('oauth', $global['router']['routes']);
        $this->assertFalse(isset($global['router']['routes']['oauth']));
        $local = include $this->localConfigPath;
        $this->assertFalse(isset($local['router']['routes']['oauth']));
        $this->assertArrayNotHasKey('db', $local['api-tools-oauth2']);
        $this->assertArrayNotHasKey('storage', $local['api-tools-oauth2']);
    }

    /**
     * @group 172
     */
    public function testRemovingOAuth2MongoConfigurationRemovesConfigurationFromEachFile()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('mongo extension must be loaded to run this test');
        }

        $toCreate = [
            'dsn_type'    => 'mongo',
            'dsn'         => 'mongodb://localhost:27017/api-tools',
            'route_match' => '/api/oauth',
        ];

        $model    = $this->createModelFromConfigArrays([], []);
        $model->create($toCreate);

        $model->remove();

        $global = include $this->globalConfigPath;
        $this->assertArrayNotHasKey('oauth', $global['router']['routes']);
        $this->assertFalse(isset($global['router']['routes']['oauth']));
        $local = include $this->localConfigPath;
        $this->assertFalse(isset($local['router']['routes']['oauth']));
        $this->assertArrayNotHasKey('mongo', $local['api-tools-oauth2']);
        $this->assertArrayNotHasKey('storage', $local['api-tools-oauth2']);
    }

    /**
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToCreateOAuth2ConfigurationWithInvalidMongoDsnRaisesException()
    {
        if (!extension_loaded('mongo')) {
            $this->markTestSkipped('mongo extension must be loaded to run this test');
        }

        $toCreate = [
            'dsn'         => 'mongodb:300.300.300.300',
            'database'    => 'wrong',
            'route_match' => '/api/oauth',
            'dsn_type'    => 'Mongo'
        ];
        $model = $this->createModelFromConfigArrays([], []);

        $this->setExpectedException('Laminas\ApiTools\Admin\Exception\InvalidArgumentException', 'DSN', 422);
        $model->create($toCreate);
    }

    /**
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToCreateOAuth2ConfigurationWithInvalidDsnRaisesException()
    {
        $toCreate = [
            'dsn'         => 'sqlite:/tmp/' . uniqid() . '/.db',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ];
        $model = $this->createModelFromConfigArrays([], []);

        $this->setExpectedException('Laminas\ApiTools\Admin\Exception\InvalidArgumentException', 'DSN', 422);
        $model->create($toCreate);
    }

    /**
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToUpdateOAuth2ConfigurationWithInvalidDsnRaisesException()
    {
        $toCreate = [
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ];
        $model = $this->createModelFromConfigArrays([], []);

        $model->create($toCreate);
        $newConfig = [
            'dsn' => 'sqlite:/tmp/' . uniqid() . '/.db',
        ];

        $this->setExpectedException('Laminas\ApiTools\Admin\Exception\InvalidArgumentException', 'DSN', 422);
        $entity = $model->update($newConfig);
    }


    public function getAuthAdapters()
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
                                'Bar'       => 'test4'
                            ]
                        ]
                    ],
                    'router' => [
                        'routes' => [
                            'oauth' => [
                                'type' => 'regex',
                                'options' => [
                                    'regex' => '(?P<oauth>(/oauth_mongo|/oauth_pdo))',
                                    'spec' => '%oauth%'
                                ]
                            ]
                        ]
                    ]
                ],
                [ // local
                    'api-tools-mvc-auth' => [
                        'authentication' => [
                            'adapters' => [
                                'test1' => [
                                    'adapter' => 'Laminas\ApiTools\MvcAuth\Authentication\HttpAdapter',
                                    'options' => [
                                        'accept_schemes' => ['basic'],
                                        'realm' => 'api',
                                        'htpasswd' => 'data/htpasswd'
                                    ]
                                ],
                                'test2' => [
                                    'adapter' => 'Laminas\ApiTools\MvcAuth\Authentication\HttpAdapter',
                                    'options' => [
                                        'accept_schemes' => ['digest'],
                                        'realm' => 'api',
                                        'digest_domains' => 'domain.com',
                                        'nonce_timeout' => 3600,
                                        'htdigest' => 'data/htpasswd',

                                    ]
                                ],
                                'test3' => [
                                    'adapter' => 'Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter',
                                    'storage' => [
                                        'adapter' => 'pdo',
                                        'route' => '/oauth_pdo',
                                        'dsn' => 'mysql:host=localhost;dbname=oauth2',
                                        'username' => 'test',
                                        'password' => 'test',
                                        'options' => [
                                            1002 => 'SET NAMES utf8'
                                        ]
                                    ]
                                ],
                                'test4' => [
                                    'adapter' => 'Laminas\ApiTools\MvcAuth\Authentication\OAuth2Adapter',
                                    'storage' => [
                                        'adapter' => 'mongo',
                                        'route' => '/oauth_mongo',
                                        'locator_name' => 'SomeServiceName',
                                        'dsn' => 'mongodb://localhost',
                                        'database' => 'oauth2',
                                        'options' => [
                                            'username' => 'username',
                                            'password' => 'password',
                                            'connectTimeoutMS' => 500,
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Test fetch all authentication adapters
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testFetchAllAuthenticationAdapter($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $result = $model->fetchAllAuthenticationAdapter();
        $this->assertTrue(is_array($result));
        $this->assertEquals(4, count($result));
        $this->assertEquals('test1', $result[0]['name']);
        $this->assertEquals('test2', $result[1]['name']);
        $this->assertEquals('test3', $result[2]['name']);
        $this->assertEquals('test4', $result[3]['name']);
    }

    /**
     * Test fetch a specific authentication adapter
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testFetchAuthenticationAdapter($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $result = $model->fetchAuthenticationAdapter('test3');
        $this->assertTrue(is_array($result));
        $this->assertEquals('test3', $result['name']);
        $this->assertEquals('oauth2', $result['type']);
        $this->assertEquals(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test3']['storage']['adapter'],
            $result['oauth2_type']
        );
        $this->assertEquals(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test3']['storage']['dsn'],
            $result['oauth2_dsn']
        );
        $this->assertEquals(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test3']['storage']['route'],
            $result['oauth2_route']
        );
        $this->assertEquals(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test3']['storage']['options'],
            $result['oauth2_options']
        );
    }

    public function getDataForAuthAdapters()
    {
        return [
            [
                'name' => 'test10',
                'type' => 'basic',
                'realm' => 'api',
                'htpasswd' => __DIR__ . '/TestAsset/htpasswd'
            ],
            [
                'name'           => 'test11',
                'type'           => 'digest',
                'realm'          => 'api',
                'digest_domains' => 'domain.com',
                'nonce_timeout'  => 3600,
                'htdigest'       => __DIR__ . '/TestAsset/htdigest'
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
                    'foo' => 'bar'
                ]
            ],
            [
                'name'                => 'test13',
                'type'                => 'oauth2',
                'oauth2_type'         => 'mongo',
                'oauth2_dsn'          => 'mongodb://localhost',
                'oauth2_database'     => 'api-tools-admin-test',
                'oauth2_route'        => '/oauth13',
                'oauth2_locator_name' => null,
                'oauth2_options'  => [
                    'foo' => 'bar'
                ]
            ],
        ];
    }

    /**
     * Test create an authentication adapter
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testCreateAuthenticationAdapter($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $data = $this->getDataForAuthAdapters();
        foreach ($data as $adapter) {
            if (isset($adapter['oauth2_type'])
                && 'mongo' === $adapter['oauth2_type']
                && ! extension_loaded('mongo')
            ) {
                // Cannot create a Mongo adapter on systems without the Mongo extension
                continue;
            }
            $result = $model->createAuthenticationAdapter($adapter);
            $this->assertTrue(is_array($result));
            $this->assertEquals($adapter, $result);
            if ('oauth2' === $result['type']) {
                $config = include $this->globalConfigPath;
                $this->assertTrue(in_array($adapter['oauth2_route'], $model->fromOAuth2RegexToArray($config)));
            }
        }
    }

    /**
     * Test update an authentication adapter
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testUpdateAuthenticationAdapter($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $data = $this->getDataForAuthAdapters();
        $data[2]['name'] = 'test1';
        $result = $model->updateAuthenticationAdapter('test1', $data[2]);
        $this->assertTrue(is_array($result));
        $this->assertEquals($data[2], $result);
        $config = include $this->globalConfigPath;
        $this->assertTrue(in_array($data[2]['oauth2_route'], $model->fromOAuth2RegexToArray($config)));
    }

    /**
     * Test remove an authentication adapter
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testRemoveAuthenticationAdapter($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $this->assertTrue($model->removeAuthenticationAdapter('test4'));
        $config = include $this->localConfigPath;
        $this->assertTrue(!isset($config['api-tools-mvc-auth']['authentication']['adapters']['test4']));
        $config = include $this->globalConfigPath;
        $this->assertTrue(!in_array(
            $local['api-tools-mvc-auth']['authentication']['adapters']['test4']['storage']['route'],
            $model->fromOAuth2RegexToArray($config)
        ));
    }

    /**
     * Test get authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testGetAuthenticationMap($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, []);

        $result = $model->getAuthenticationMap('Status', 1);
        $this->assertEquals($global['api-tools-mvc-auth']['authentication']['map']['Status\V1'], $result);
        $result = $model->getAuthenticationMap('Foo');
        $this->assertEquals($global['api-tools-mvc-auth']['authentication']['map']['Foo'], $result);
        $result = $model->getAuthenticationMap('User', 1);
        $this->assertFalse($result);
        $result = $model->getAuthenticationMap('Test');
        $this->assertFalse($result);
    }

    /**
     * Test add authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testAddAuthenticationMap($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $this->assertTrue($model->saveAuthenticationMap('test1', 'User', 1));
        $this->assertEquals('test1', $model->getAuthenticationMap('User', 1));
    }

    /**
     * Test add invalid authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     * @expectedException Laminas\ApiTools\Admin\Exception\InvalidArgumentException
     */
    public function testAddInvalidAuthenticationMap($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);
        $model->saveAuthenticationMap('test', 'Foo', 1);
    }

    /**
     * Test update authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testUpdateAuthenticationMap($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $this->assertTrue($model->saveAuthenticationMap('test4', 'Status', 1));
        $this->assertEquals('test4', $model->getAuthenticationMap('Status', 1));
        $this->assertTrue($model->saveAuthenticationMap('test1', 'Foo'));
        $this->assertEquals('test1', $model->getAuthenticationMap('Foo'));
    }

    /**
     * Test remove authentication map
     * Since Laminas API Tools 1.1
     *
     * @dataProvider getAuthAdapters
     */
    public function testRemoveAuthenticationMap($global, $local)
    {
        $model = $this->createModelFromConfigArrays($global, $local);

        $this->assertTrue($model->removeAuthenticationMap('Status', 1));
        $this->assertFalse($model->getAuthenticationMap('Status', 1));
        $config = include $this->globalConfigPath;
        $this->assertTrue(!isset($config['api-tools-mvc-auth']['authentication']['map']['Status\V1']));
        $this->assertTrue($model->removeAuthenticationMap('Foo'));
        $this->assertFalse($model->getAuthenticationMap('Foo'));
        $config = include $this->globalConfigPath;
        $this->assertTrue(!isset($config['api-tools-mvc-auth']['authentication']['map']['Foo']));
    }

    public function getOldAuthenticationConfig()
    {
        return [
            'http_basic' => [
                'api-tools-mvc-auth' => [
                    'authentication' => [
                        'http' => [
                            'accept_schemes' => ['basic'],
                            'realm' => 'My Web Site',
                            'htpasswd' => __DIR__ . '/TestAsset/htpasswd'
                        ]
                    ]
                ]
            ],
            'http_digest' => [
                'api-tools-mvc-auth' => [
                    'authentication' => [
                        'http' => [
                            'accept_schemes' => ['digest'],
                            'realm' => 'My Web Site',
                            'digest_domains' => 'domain.com',
                            'nonce_timeout' => 3600,
                            'htdigest' => __DIR__ . '/TestAsset/htdigest'
                        ]
                    ]
                ]
            ],
            'oauth2_pdo' => [
                'api-tools-oauth2' => [
                    'storage' => 'Laminas\\ApiTools\\OAuth2\\Adapter\\PdoAdapter',
                    'db' => [
                        'dsn_type'  => 'PDO',
                        'dsn'       => 'sqlite:/' . __DIR__ . '/TestAsset/db.sqlite',
                        'username'  => null,
                        'password'  => null
                    ]
                ]
            ],
            'oauth2_mongo' => [
                'api-tools-oauth2' => [
                    'storage' => 'Laminas\\ApiTools\\OAuth2\\Adapter\\MongoAdapter',
                    'mongo' => [
                        'dsn_type'     => 'Mongo',
                        'dsn'          => 'mongodb://localhost',
                        'database'     => 'api-tools-admin-test',
                        'locator_name' => 'MongoDB'
                    ]
                ]
            ]
        ];
    }

    /**
     * Test transform old authentication configuration in authentication per APIs
     * Since Laminas API Tools 1.1
     */
    public function testTransformAuthPerApis()
    {
        $global = [
            'router' => [
                'routes' => [
                    'oauth' => [
                        'options' => [
                            'route' => '/oauth'
                        ]
                    ]
                ]
            ]
        ];

        foreach ($this->getOldAuthenticationConfig() as $name => $local) {
            $model = $this->createModelFromConfigArrays($global, $local);

            $this->assertEquals($name, $model->transformAuthPerApis());

            // Old authentication is empty
            $this->assertFalse($model->fetch());

            // New authentication adapter exists
            $result = $model->fetchAuthenticationAdapter($name);
            $this->assertEquals($name, $result['name']);

            // Authentication map exists
            $this->assertEquals($result['name'], $model->getAuthenticationMap('Foo', 1));
            $this->assertEquals($result['name'], $model->getAuthenticationMap('Foo', 2));
        }
    }

    public function testCustomAuthAdapters()
    {
        $local = [
            'api-tools-mvc-auth' => [
                'authentication' => [
                    'adapters' => [
                        'custom1' => [
                            'adapter' => 'Laminas\\ApiTools\\MvcAuth\\Authentication\\OAuth2Adapter',
                            'storage' => [
                                'storage' => 'MyAuth\OAuth2Adapter',
                                'route' => '/oauth',
                            ],
                        ],
                        'custom2' => [
                            'adapter' => 'MyAuth\\CustomAuthAdapter',
                            'storage' => [
                                'storage' => 'MyAuth\OAuth2Adapter',
                                'route' => '/oauth',
                            ],
                        ],
                    ],
                ]
            ]
        ];
        $model = $this->createModelFromConfigArrays([], $local);

        $result = $model->fetchAllAuthenticationAdapter();
        $this->assertEquals('custom', $result[0]['oauth2_type']);
        $this->assertEquals('custom', $result[1]['type']);
        $this->assertEquals('/oauth', $result[1]['route']);
    }
}
