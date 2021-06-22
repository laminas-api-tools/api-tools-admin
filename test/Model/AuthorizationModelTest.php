<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use AuthConf;
use AuthConfDefaults;
use AuthConfWithConfig;
use FooConf;
use Laminas\ApiTools\Admin\Model\AuthorizationEntity;
use Laminas\ApiTools\Admin\Model\AuthorizationModel;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\Config\Writer\PhpArray;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function copy;
use function count;
use function glob;
use function is_dir;
use function preg_match;
use function rmdir;
use function scandir;
use function sprintf;
use function unlink;

class AuthorizationModelTest extends TestCase
{
    /** @var string */
    private $module;
    /** @var ResourceFactory */
    private $resource;
    /** @var AuthorizationModel */
    private $model;

    /**
     * Remove a directory even if not empty (recursive delete)
     */
    protected function removeDir(string $dir): bool
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

    protected function cleanUpAssets(): void
    {
        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
        $configPath = $basePath . '/config';
        foreach (glob(sprintf('%s/src/%s/V*', $basePath, $this->module)) as $dir) {
            $this->removeDir($dir);
        }
        copy($configPath . '/module.config.php.dist', $configPath . '/module.config.php');
    }

    public function setUpModel(string $module): void
    {
        $this->module = $module;
        $this->cleanUpAssets();

        $modules = [
            'FooConf'            => new FooConf\Module(),
            'AuthConf'           => new AuthConf\Module(),
            'AuthConfDefaults'   => new AuthConfDefaults\Module(),
            'AuthConfWithConfig' => new AuthConfWithConfig\Module(),
        ];

        $moduleEntity  = new ModuleEntity($this->module);
        $moduleManager = $this->getMockBuilder(ModuleManager::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $writer         = new PhpArray();
        $moduleUtils    = new ModuleUtils($moduleManager);
        $modules1       = new ModulePathSpec($moduleUtils);
        $this->resource = new ResourceFactory($moduleUtils, $writer);
        $this->model    = new AuthorizationModel(
            $moduleEntity,
            $modules1,
            $this->resource->factory($this->module)
        );
    }

    public function tearDown(): void
    {
        $this->cleanUpAssets();
    }

    /**
     * @param array<string, bool> $privileges
     */
    public function assertDefaultPrivileges(array $privileges): void
    {
        self::assertEquals([
            'GET'    => false,
            'POST'   => false,
            'PUT'    => false,
            'PATCH'  => false,
            'DELETE' => false,
        ], $privileges);
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    protected function mapConfigToPayload(array $config): array
    {
        foreach ($config as $key => $value) {
            // Replace keys to match what the API is going to send back and forth
            if (isset($value['actions'])) {
                foreach ($value['actions'] as $action => $privileges) {
                    $newKey          = sprintf('%s::%s', $key, $action);
                    $config[$newKey] = $privileges;
                }
            }
            if (isset($value['entity'])) {
                $newKey          = sprintf('%s::__entity__', $key);
                $config[$newKey] = $value['entity'];
            }
            if (isset($value['collection'])) {
                $newKey          = sprintf('%s::__collection__', $key);
                $config[$newKey] = $value['collection'];
            }
            unset($config[$key]);
        }
        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapEntityToConfig(AuthorizationEntity $entity): array
    {
        $normalized = [];
        foreach ($entity->getArrayCopy() as $spec => $privileges) {
            preg_match('/^(?P<service>[^:]+)(::(?P<action>.*))?$/', $spec, $matches);
            if (! isset($matches['action'])) {
                $normalized[$matches['service']]['actions']['index'] = $privileges;
            } elseif (preg_match('/^__(?P<type>collection|entity)__$/', $matches['action'], $actionMatches)) {
                $type                                   = $actionMatches['type'];
                $normalized[$matches['service']][$type] = $privileges;
            } else {
                $normalized[$matches['service']]['actions'][$matches['action']] = $privileges;
            }
        }
        return $normalized;
    }

    public function testFetchReturnsEmptyAuthorizationEntityWhenNoServicesPresent(): void
    {
        $this->setUpModel('FooConf');
        $entity = $this->model->fetch();
        self::assertInstanceOf(AuthorizationEntity::class, $entity);
        self::assertEquals(0, count($entity));
    }

    public function testFetchReturnsPopulatedAuthorizationEntityWhenServicesArePresent(): void
    {
        $this->setUpModel('AuthConf');
        $entity = $this->model->fetch();
        self::assertInstanceOf(AuthorizationEntity::class, $entity);
        self::assertEquals(6, count($entity));
        $expected = [
            'AuthConf\V1\Rest\Foo\Controller::__entity__',
            'AuthConf\V1\Rest\Foo\Controller::__collection__',
            'AuthConf\V1\Rest\Bar\Controller::__entity__',
            'AuthConf\V1\Rest\Bar\Controller::__collection__',
            'AuthConf\V1\Rpc\Baz\Controller::baz',
            'AuthConf\V1\Rpc\Bat\Controller::bat',
        ];
        $actual   = [];
        foreach ($entity as $serviceName => $privileges) {
            $actual[] = $serviceName;
            self::assertDefaultPrivileges($privileges);
        }
        self::assertEquals($expected, $actual);
    }

    public function testUsesIndexAsActionForRpcServicesIfActionCannotBeDetermined(): void
    {
        $this->setUpModel('AuthConfDefaults');
        $entity = $this->model->fetch();
        self::assertInstanceOf(AuthorizationEntity::class, $entity);
        self::assertEquals(6, count($entity));
        self::assertTrue($entity->has('AuthConfDefaults\V1\Rpc\Bat\Controller::index'));
    }

    public function testFetchAcceptsVersionAndReturnsAuthorizationListByVersion(): void
    {
        $this->setUpModel('AuthConf');
        $entity = $this->model->fetch(2); // <- VERSION!
        self::assertInstanceOf(AuthorizationEntity::class, $entity);
        self::assertEquals(9, count($entity));
        $expected = [
            'AuthConf\V2\Rest\Foo\Controller::__entity__',
            'AuthConf\V2\Rest\Foo\Controller::__collection__',
            'AuthConf\V2\Rest\Bar\Controller::__entity__',
            'AuthConf\V2\Rest\Bar\Controller::__collection__',
            'AuthConf\V2\Rest\New\Controller::__entity__',
            'AuthConf\V2\Rest\New\Controller::__collection__',
            'AuthConf\V2\Rpc\Baz\Controller::baz',
            'AuthConf\V2\Rpc\Bat\Controller::bat',
            'AuthConf\V2\Rpc\New\Controller::new',
        ];
        $actual   = [];
        foreach ($entity as $serviceName => $privileges) {
            $actual[] = $serviceName;
            self::assertDefaultPrivileges($privileges);
        }
        self::assertEquals($expected, $actual);
    }

    public function testAccuratelyRepresentsExistingPrivileges(): void
    {
        $this->setUpModel('AuthConfWithConfig');

        // Get config as it exists to begin
        $config = $this->resource->factory($this->module)->fetch(true);
        $config = $config['api-tools-mvc-auth']['authorization'];

        // Have the model fetch it
        $entity = $this->model->fetch();
        self::assertInstanceOf(AuthorizationEntity::class, $entity);
        $entity = $this->mapEntityToConfig($entity);
        self::assertEquals($config, $entity);
    }

    public function testCanUpdatePrivileges(): void
    {
        $this->setUpModel('AuthConfWithConfig');

        // Get config as it exists to begin
        $config = $this->resource->factory($this->module)->fetch(true);

        // Toggle all privileges
        $newPrivileges = $this->mapConfigToPayload($config['api-tools-mvc-auth']['authorization']);
        foreach ($newPrivileges as $serviceName => $privileges) {
            foreach ($privileges as $method => $flag) {
                $newPrivileges[$serviceName][$method] = ! $flag;
            }
        }

        $entity = $this->model->update($newPrivileges);
        self::assertInstanceOf(AuthorizationEntity::class, $entity);

        // Test that the entity matches the new privileges
        self::assertEquals($newPrivileges, $entity->getArrayCopy());

        // Test that the stored configuration has been updated as well
        $config = $this->resource->factory($this->module)->fetch(true);
        $config = $config['api-tools-mvc-auth']['authorization'];

        $expected = $this->mapEntityToConfig($entity);

        self::assertEquals($expected, $config);
    }
}
