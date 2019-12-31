<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Model\AuthenticationModel;
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
            mkdir($this->configPath, 0777, true);
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
        return new AuthenticationModel($globalConfig, $localConfig);
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
        $toCreate = array(
            'accept_schemes' => array('basic'),
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        );

        $model    = $this->createModelFromConfigArrays(array(), array());
        $model->create($toCreate);

        $global = include($this->globalConfigPath);
        $this->assertAuthenticationConfigEquals('http', array(
            'accept_schemes' => array('basic'),
            'realm'          => 'laminascon',
        ), $global);

        $local  = include($this->localConfigPath);
        $this->assertAuthenticationConfigEquals('http', array(
            'htpasswd'       => __DIR__ . '/htpasswd',
        ), $local);
    }

    public function testCanRetrieveAuthenticationConfig()
    {
        $globalSeedConfig = array(
            'api-tools-mvc-auth' => array(
                'authentication' => array(
                    'http' => array(
                        'accept_schemes' => array('basic'),
                        'realm'          => 'laminascon',
                    ),
                ),
            ),
        );
        $localSeedConfig = array(
            'api-tools-mvc-auth' => array(
                'authentication' => array(
                    'http' => array(
                        'htpasswd' => __DIR__ . '/htpasswd',
                    ),
                ),
            ),
        );
        $model  = $this->createModelFromConfigArrays($globalSeedConfig, $localSeedConfig);
        $entity = $model->fetch();
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\AuthenticationEntity', $entity);
        $expected = array_merge(
            array('type' => 'http_basic'),
            $globalSeedConfig['api-tools-mvc-auth']['authentication']['http'],
            $localSeedConfig['api-tools-mvc-auth']['authentication']['http']
        );
        $this->assertEquals($expected, $entity->getArrayCopy());
    }

    public function testUpdatesGlobalAndLocalConfigWhenUpdating()
    {
        $toCreate = array(
            'accept_schemes' => array('basic'),
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        );
        $model = $this->createModelFromConfigArrays(array(), array());
        $model->create($toCreate);

        $newConfig = array(
            'realm'    => 'api',
            'htpasswd' => sys_get_temp_dir() . '/htpasswd',
        );
        $entity = $model->update($newConfig);

        // Ensure the entity returned from the update is what we expect
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\AuthenticationEntity', $entity);
        $expected = array_merge(array('type' => 'http_basic'), $toCreate, $newConfig);
        $this->assertEquals($expected, $entity->getArrayCopy());

        // Ensure fetching the entity after an update will return what we expect
        $config = include $this->globalConfigPath;
        $this->assertAuthenticationConfigEquals('http', array(
            'accept_schemes' => array('basic'),
            'realm'          => 'api',
        ), $config);

        $config = include $this->localConfigPath;
        $this->assertAuthenticationConfigEquals('http', array('htpasswd' => sys_get_temp_dir() . '/htpasswd'), $config);
    }

    public function testRemoveDeletesConfigurationFromBothLocalAndGlobalConfigFiles()
    {
        $toCreate = array(
            'accept_schemes' => array('basic'),
            'realm'          => 'laminascon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        );
        $model    = $this->createModelFromConfigArrays(array(), array());
        $model->create($toCreate);

        $model->remove();
        $global = include $this->globalConfigPath;
        $this->assertArrayNotHasKey('http', $global['api-tools-mvc-auth']['authentication']);
        $local = include $this->localConfigPath;
        $this->assertArrayNotHasKey('http', $local['api-tools-mvc-auth']['authentication']);
    }

    public function testCreatingOAuth2ConfigurationWritesToEachConfigFile()
    {
        $toCreate = array(
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        );

        $model    = $this->createModelFromConfigArrays(array(), array());
        $model->create($toCreate);

        $global = include($this->globalConfigPath);
        $this->assertArrayHasKey('router', $global);
        $this->assertArrayHasKey('routes', $global['router']);
        $this->assertArrayHasKey('oauth', $global['router']['routes']);
        $this->assertArrayHasKey('options', $global['router']['routes']['oauth']);
        $this->assertArrayHasKey('route', $global['router']['routes']['oauth']['options']);
        $this->assertEquals('/api/oauth', $global['router']['routes']['oauth']['options']['route'], var_export($global, 1));

        $local  = include($this->localConfigPath);
        $this->assertEquals(array(
            'storage' => 'Laminas\ApiTools\OAuth2\Adapter\PdoAdapter',
            'db' => array(
                'dsn_type'    => 'PDO',
                'dsn'         => 'sqlite::memory:',
                'username'    => 'me',
                'password'    => 'too',
            ),
        ), $local['api-tools-oauth2']);
    }

    public function testCreatingOAuth2ConfigurationWritesToEachConfigFileForMongo()
    {
        $toCreate = array(
            'dsn'         => 'mongodb://localhost:27017',
            'database'    => 'apiToolsTest',
            'dsn_type'    => 'Mongo',
            'route_match' => '/api/oauth',
        );

        $model    = $this->createModelFromConfigArrays(array(), array());
        $model->create($toCreate);

        $global = include($this->globalConfigPath);
        $this->assertArrayHasKey('router', $global);
        $this->assertArrayHasKey('routes', $global['router']);
        $this->assertArrayHasKey('oauth', $global['router']['routes']);
        $this->assertArrayHasKey('options', $global['router']['routes']['oauth']);
        $this->assertArrayHasKey('route', $global['router']['routes']['oauth']['options']);
        $this->assertEquals('/api/oauth', $global['router']['routes']['oauth']['options']['route'], var_export($global, 1));

        $local  = include($this->localConfigPath);
        $this->assertEquals(array(
            'storage' => 'Laminas\ApiTools\OAuth2\Adapter\MongoAdapter',
            'mongo' => array(
                'dsn_type'    => 'Mongo',
                'dsn'         => 'mongodb://localhost:27017',
                'username'    => null,
                'password'    => null,
                'database'    => 'apiToolsTest',
            ),
        ), $local['api-tools-oauth2']);
    }

    public function testRemovingOAuth2ConfigurationRemovesConfigurationFromEachFile()
    {
        $toCreate = array(
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        );

        $model    = $this->createModelFromConfigArrays(array(), array());
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
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToCreateOAuth2ConfigurationWithInvalidMongoDsnRaisesException()
    {
        $toCreate = array(
            'dsn'         => 'mongodb:300.300.300.300',
            'database'    => 'wrong',
            'route_match' => '/api/oauth',
            'dsn_type'    => 'Mongo'
        );
        $model = $this->createModelFromConfigArrays(array(), array());

        $this->setExpectedException('Laminas\ApiTools\Admin\Exception\InvalidArgumentException', 'DSN', 422);
        $model->create($toCreate);
    }

    /**
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToCreateOAuth2ConfigurationWithInvalidDsnRaisesException()
    {
        $toCreate = array(
            'dsn'         => 'sqlite:/tmp/' . uniqid() . '/.db',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        );
        $model = $this->createModelFromConfigArrays(array(), array());

        $this->setExpectedException('Laminas\ApiTools\Admin\Exception\InvalidArgumentException', 'DSN', 422);
        $model->create($toCreate);
    }

    /**
     * @group api-tools-oauth2-19
     */
    public function testAttemptingToUpdateOAuth2ConfigurationWithInvalidDsnRaisesException()
    {
        $toCreate = array(
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        );
        $model = $this->createModelFromConfigArrays(array(), array());

        $model->create($toCreate);
        $newConfig = array(
            'dsn' => 'sqlite:/tmp/' . uniqid() . '/.db',
        );

        $this->setExpectedException('Laminas\ApiTools\Admin\Exception\InvalidArgumentException', 'DSN', 422);
        $entity = $model->update($newConfig);
    }
}
