<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use BarConf;
use Laminas\ApiTools\Admin\Model\DbConnectedRestServiceEntity;
use Laminas\ApiTools\Admin\Model\DbConnectedRestServiceModel;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\RestServiceEntity;
use Laminas\ApiTools\Admin\Model\RestServiceModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\Config\Writer\PhpArray;
use Laminas\EventManager\Event;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;

use function array_diff;
use function copy;
use function file_exists;
use function glob;
use function is_dir;
use function rmdir;
use function scandir;
use function sprintf;
use function unlink;
use function var_export;

class DbConnectedRestServiceModelTest extends TestCase
{
    use ProphecyTrait;

    /** @var DbConnectedRestServiceModel */
    private $model;
    /** @var string */
    private $module;

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

    public function setUp(): void
    {
        $this->module = 'BarConf';
        $this->cleanUpAssets();

        $modules = [
            'BarConf' => new BarConf\Module(),
        ];

        $moduleEntity  = new ModuleEntity($this->module, [], [], false);
        $moduleManager = $this->prophesize(ModuleManager::class);
        $moduleManager->getLoadedModules()->willReturn($modules);

        $writer      = new PhpArray();
        $moduleUtils = new ModuleUtils($moduleManager->reveal());
        $modules1    = new ModulePathSpec($moduleUtils);
        $resource    = new ResourceFactory($moduleUtils, $writer);
        $codeRest    = new RestServiceModel(
            $moduleEntity,
            $modules1,
            $resource->factory('BarConf')
        );
        $this->model = new DbConnectedRestServiceModel($codeRest);
        $codeRest->getEventManager()->attach('fetch', [$this->model, 'onFetch']);
    }

    public function tearDown(): void
    {
        $this->cleanUpAssets();
    }

    public function getCreationPayload(): DbConnectedRestServiceEntity
    {
        $payload = new DbConnectedRestServiceEntity();
        $payload->exchangeArray([
            'adapter_name'               => 'DB\Barbaz',
            'table_name'                 => 'barbaz',
            'hydrator_name'              => 'ObjectPropertyHydrator',
            'entity_identifier_name'     => 'barbaz_id',
            'resource_http_methods'      => ['GET', 'PATCH'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_query_whitelist' => ['sort', 'filter'],
            'page_size'                  => 10,
            'page_size_param'            => 'p',
            'selector'                   => 'HalJson',
            'accept_whitelist'           => ['application/json', 'application/*+json'],
            'content_type_whitelist'     => ['application/json'],
        ]);
        return $payload;
    }

    public function testCreateServiceReturnsDbConnectedRestServiceEntity(): void
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        self::assertSame($originalEntity, $result);

        self::assertEquals('BarConf\V1\Rest\Barbaz\Controller', $result->controllerServiceName);
        self::assertEquals('BarConf\V1\Rest\Barbaz\BarbazResource', $result->resourceClass);
        self::assertEquals('BarConf\V1\Rest\Barbaz\BarbazEntity', $result->entityClass);
        self::assertEquals('BarConf\V1\Rest\Barbaz\BarbazCollection', $result->collectionClass);
        self::assertEquals('BarConf\V1\Rest\Barbaz\BarbazResource\Table', $result->tableService);
        self::assertEquals('barbaz', $result->tableName);
        self::assertEquals('bar-conf.rest.barbaz', $result->routeName);
    }

    public function testEntityCreatedViaCreateServiceIsAnArrayObjectExtension(): void
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);
        include __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz/BarbazEntity.php';
        $r      = new ReflectionClass('BarConf\V1\Rest\Barbaz\BarbazEntity');
        $parent = $r->getParentClass();
        self::assertInstanceOf('ReflectionClass', $parent);
        self::assertEquals('ArrayObject', $parent->getName());
    }

    public function testCreateServiceWritesDbConnectedConfigurationUsingResourceClassAsKey(): void
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config         = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        self::assertArrayHasKey('api-tools', $config);
        self::assertArrayHasKey('db-connected', $config['api-tools']);
        self::assertArrayHasKey($result->resourceClass, $config['api-tools']['db-connected']);

        $resourceConfig = $config['api-tools']['db-connected'][$result->resourceClass];
        self::assertArrayHasKey('table_name', $resourceConfig);
        self::assertArrayHasKey('hydrator_name', $resourceConfig);
        self::assertArrayHasKey('controller_service_name', $resourceConfig);

        self::assertEquals('barbaz', $resourceConfig['table_name']);
        self::assertEquals($result->hydratorName, $resourceConfig['hydrator_name']);
        self::assertEquals($result->controllerServiceName, $resourceConfig['controller_service_name']);
    }

    public function testCreateServiceWritesRestConfigurationWithEntityAndCollectionClass(): void
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config         = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        self::assertArrayHasKey('api-tools-rest', $config);
        self::assertArrayHasKey($result->controllerServiceName, $config['api-tools-rest']);

        $restConfig = $config['api-tools-rest'][$result->controllerServiceName];
        self::assertArrayHasKey('entity_class', $restConfig);
        self::assertArrayHasKey('collection_class', $restConfig);

        self::assertEquals($result->entityClass, $restConfig['entity_class']);
        self::assertEquals($result->collectionClass, $restConfig['collection_class']);
    }

    public function testCreateServiceWritesHalConfigurationWithHydrator(): void
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config         = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        self::assertArrayHasKey('api-tools-hal', $config);
        self::assertArrayHasKey('metadata_map', $config['api-tools-hal']);
        self::assertArrayHasKey($result->entityClass, $config['api-tools-hal']['metadata_map']);

        $halConfig = $config['api-tools-hal']['metadata_map'][$result->entityClass];
        self::assertArrayHasKey('hydrator', $halConfig);

        self::assertEquals($result->hydratorName, $halConfig['hydrator']);
    }

    public function testCreateServiceDoesNotCreateResourceClass(): void
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);
        self::assertFalse(
            file_exists(__DIR__ . '/TestAsset/module/BarConf/src/BarConf/Rest/Barbaz/BarbazResource.php')
        );
    }

    public function testOnFetchWillRecastEntityToDbConnectedIfDbConnectedConfigurationExists(): void
    {
        $originalData = [
            'controller_service_name' => 'BarConf\Rest\Barbaz\Controller',
            'resource_class'          => 'BarConf\Rest\Barbaz\BarbazResource',
            'route_name'              => 'bar-conf.rest.barbaz',
            'route_match'             => '/api/barbaz',
            'entity_class'            => 'BarConf\Rest\Barbaz\BarbazEntity',
        ];
        $entity       = new RestServiceEntity();
        $entity->exchangeArray($originalData);
        $config = [
            'api-tools' => [
                'db-connected' => [
                    'BarConf\Rest\Barbaz\BarbazResource' => [
                        'adapter_name'  => 'Db\Barbaz',
                        'table_name'    => 'barbaz',
                        'hydrator_name' => 'ObjectPropertyHydrator',
                    ],
                ],
            ],
        ];

        $event = new Event();
        $event->setParam('entity', $entity);
        $event->setParam('config', $config);
        $result = $this->model->onFetch($event);
        self::assertInstanceOf(DbConnectedRestServiceEntity::class, $result);
        $asArray = $result->getArrayCopy();
        foreach ($originalData as $key => $value) {
            self::assertArrayHasKey($key, $asArray);
            if ($key === 'resource_class') {
                self::assertNull(
                    $asArray[$key],
                    sprintf("Failed asserting that resource_class is null\nEntity is: %s\n", var_export($asArray, true))
                );
                continue;
            }
            self::assertEquals(
                $value,
                $asArray[$key],
                sprintf("Failed testing key '%s'\nEntity is: %s\n", $key, var_export($asArray, true))
            );
        }
        foreach ($config['api-tools']['db-connected']['BarConf\Rest\Barbaz\BarbazResource'] as $key => $value) {
            self::assertArrayHasKey($key, $asArray);
            self::assertEquals($value, $asArray[$key]);
        }
        self::assertArrayHasKey('table_service', $asArray);
        self::assertEquals($entity->resourceClass . '\\Table', $asArray['table_service']);
    }

    /**
     * @group 166
     */
    public function testOnFetchWillRetainResourceClassIfEventFetchFlagIsFalse(): void
    {
        $originalData = [
            'controller_service_name' => 'BarConf\Rest\Barbaz\Controller',
            'resource_class'          => 'BarConf\Rest\Barbaz\BarbazResource',
            'route_name'              => 'bar-conf.rest.barbaz',
            'route_match'             => '/api/barbaz',
            'entity_class'            => 'BarConf\Rest\Barbaz\BarbazEntity',
        ];
        $entity       = new RestServiceEntity();
        $entity->exchangeArray($originalData);
        $config = [
            'api-tools' => [
                'db-connected' => [
                    'BarConf\Rest\Barbaz\BarbazResource' => [
                        'adapter_name'  => 'Db\Barbaz',
                        'table_name'    => 'barbaz',
                        'hydrator_name' => 'ObjectPropertyHydrator',
                    ],
                ],
            ],
        ];

        $event = new Event();
        $event->setParam('entity', $entity);
        $event->setParam('config', $config);
        $event->setParam('fetch', false);
        $result = $this->model->onFetch($event);

        self::assertInstanceOf(DbConnectedRestServiceEntity::class, $result);
        self::assertEquals('BarConf\Rest\Barbaz\BarbazResource', $result->resourceClass);
        $asArray = $result->getArrayCopy();
        self::assertArrayHasKey('resource_class', $asArray);
        self::assertEquals('BarConf\Rest\Barbaz\BarbazResource', $asArray['resource_class']);
    }

    public function testUpdateServiceReturnsUpdatedDbConnectedRestServiceEntity(): void
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = [
            'table_service' => 'My\Custom\Table',
            'adapter_name'  => 'My\Db',
            'hydrator_name' => 'ClassMethodsHydrator',
        ];
        $originalEntity->exchangeArray($newProps);
        $result = $this->model->updateService($originalEntity);

        self::assertInstanceOf(DbConnectedRestServiceEntity::class, $result);
        self::assertNotSame($originalEntity, $result);
        self::assertEquals($newProps['table_service'], $result->tableService);
        self::assertEquals($newProps['adapter_name'], $result->adapterName);
        self::assertEquals($newProps['hydrator_name'], $result->hydratorName);
    }

    public function testUpdateServiceUpdatesDbConnectedConfiguration(): void
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = [
            'table_service' => 'My\Custom\Table',
            'adapter_name'  => 'My\Db',
            'hydrator_name' => 'ClassMethodsHydrator',
        ];
        $originalEntity->exchangeArray($newProps);
        $result = $this->model->updateService($originalEntity);

        self::assertInstanceOf(DbConnectedRestServiceEntity::class, $result);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools', $config);
        self::assertArrayHasKey('db-connected', $config['api-tools']);
        self::assertArrayHasKey('BarConf\V1\Rest\Barbaz\BarbazResource', $config['api-tools']['db-connected']);

        $resourceConfig = $config['api-tools']['db-connected']['BarConf\V1\Rest\Barbaz\BarbazResource'];
        self::assertArrayHasKey('adapter_name', $resourceConfig);
        self::assertArrayHasKey('table_service', $resourceConfig);
        self::assertArrayHasKey('table_name', $resourceConfig);
        self::assertArrayHasKey('hydrator_name', $resourceConfig);

        self::assertEquals($newProps['adapter_name'], $resourceConfig['adapter_name']);
        self::assertEquals($newProps['table_service'], $resourceConfig['table_service']);
        self::assertEquals('barbaz', $resourceConfig['table_name']);
        self::assertEquals($newProps['hydrator_name'], $resourceConfig['hydrator_name']);
    }

    /**
     * @group 166
     */
    public function testUpdateServiceUpdatesEntityIdentifierNameAndHydrator(): void
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = [
            'entity_identifier_name' => 'id',
            'hydrator_name'          => ClassMethodsHydrator::class,
        ];
        $originalEntity->exchangeArray($newProps);
        $this->model->updateService($originalEntity);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools', $config);
        self::assertArrayHasKey('db-connected', $config['api-tools']);
        self::assertArrayHasKey('BarConf\V1\Rest\Barbaz\BarbazResource', $config['api-tools']['db-connected']);

        $resourceConfig = $config['api-tools']['db-connected']['BarConf\V1\Rest\Barbaz\BarbazResource'];
        self::assertEquals($newProps['entity_identifier_name'], $resourceConfig['entity_identifier_name']);
        self::assertEquals($newProps['hydrator_name'], $resourceConfig['hydrator_name']);
    }

    public function testDeleteServiceRemovesDbConnectedConfigurationForEntity(): void
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);
        $this->model->deleteService($originalEntity);

        $config     = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $barbazPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz';

        self::assertTrue(file_exists($barbazPath));
        self::assertArrayHasKey('api-tools', $config);
        self::assertArrayHasKey('db-connected', $config['api-tools']);
        self::assertArrayNotHasKey($originalEntity->resourceClass, $config['api-tools']['db-connected']);
    }

    public function testDeleteServiceRecursive(): void
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);
        $this->model->deleteService($originalEntity, true);

        $barbazPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz';
        self::assertFalse(file_exists($barbazPath));
    }

    public function testCreateServiceWithUnderscoreInNameNormalizesClassNamesToCamelCase(): void
    {
        $originalEntity = $this->getCreationPayload();
        $originalEntity->exchangeArray(['table_name' => 'bar_baz']);

        $result = $this->model->createService($originalEntity);
        self::assertSame($originalEntity, $result);

        self::assertEquals('BarConf\V1\Rest\BarBaz\Controller', $result->controllerServiceName);
        self::assertEquals('BarConf\V1\Rest\BarBaz\BarBazResource', $result->resourceClass);
        self::assertEquals('BarConf\V1\Rest\BarBaz\BarBazEntity', $result->entityClass);
        self::assertEquals('BarConf\V1\Rest\BarBaz\BarBazCollection', $result->collectionClass);
        self::assertEquals('BarConf\V1\Rest\BarBaz\BarBazResource\Table', $result->tableService);
        self::assertEquals('bar_baz', $result->tableName);
        self::assertEquals('bar-conf.rest.bar-baz', $result->routeName);
    }
}
