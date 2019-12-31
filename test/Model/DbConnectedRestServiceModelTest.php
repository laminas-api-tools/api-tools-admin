<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

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
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;

class DbConnectedRestServiceModelTest extends TestCase
{
    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return boolean
     */
    protected function removeDir($dir)
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

    protected function cleanUpAssets()
    {
        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
        $configPath = $basePath . '/config';
        $srcPath    = $basePath . '/src';
        foreach (glob(sprintf('%s/src/%s/V*', $basePath, $this->module)) as $dir) {
            $this->removeDir($dir);
        }
        copy($configPath . '/module.config.php.dist', $configPath . '/module.config.php');
    }

    public function setUp()
    {
        $this->module = 'BarConf';
        $this->cleanUpAssets();

        $modules = [
            'BarConf' => new BarConf\Module()
        ];

        $this->moduleEntity  = new ModuleEntity($this->module, [], [], false);
        $this->moduleManager = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer   = new PhpArray();
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->modules  = new ModulePathSpec($moduleUtils);
        $this->resource = new ResourceFactory($moduleUtils, $this->writer);
        $this->codeRest = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BarConf')
        );
        $this->model    = new DbConnectedRestServiceModel($this->codeRest);
        $this->codeRest->getEventManager()->attach('fetch', [$this->model, 'onFetch']);
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    public function getCreationPayload()
    {
        $payload = new DbConnectedRestServiceEntity();
        $payload->exchangeArray([
            'adapter_name'               => 'DB\Barbaz',
            'table_name'                 => 'barbaz',
            'hydrator_name'              => 'ObjectProperty',
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

    public function testCreateServiceReturnsDbConnectedRestServiceEntity()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $this->assertSame($originalEntity, $result);

        $this->assertEquals('BarConf\V1\Rest\Barbaz\Controller', $result->controllerServiceName);
        $this->assertEquals('BarConf\V1\Rest\Barbaz\BarbazResource', $result->resourceClass);
        $this->assertEquals('BarConf\V1\Rest\Barbaz\BarbazEntity', $result->entityClass);
        $this->assertEquals('BarConf\V1\Rest\Barbaz\BarbazCollection', $result->collectionClass);
        $this->assertEquals('BarConf\V1\Rest\Barbaz\BarbazResource\Table', $result->tableService);
        $this->assertEquals('barbaz', $result->tableName);
        $this->assertEquals('bar-conf.rest.barbaz', $result->routeName);
    }

    public function testEntityCreatedViaCreateServiceIsAnArrayObjectExtension()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        include __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz/BarbazEntity.php';
        $r = new ReflectionClass('BarConf\V1\Rest\Barbaz\BarbazEntity');
        $parent = $r->getParentClass();
        $this->assertInstanceOf('ReflectionClass', $parent);
        $this->assertEquals('ArrayObject', $parent->getName());
    }

    public function testCreateServiceWritesDbConnectedConfigurationUsingResourceClassAsKey()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('api-tools', $config);
        $this->assertArrayHasKey('db-connected', $config['api-tools']);
        $this->assertArrayHasKey($result->resourceClass, $config['api-tools']['db-connected']);

        $resourceConfig = $config['api-tools']['db-connected'][$result->resourceClass];
        $this->assertArrayHasKey('table_name', $resourceConfig);
        $this->assertArrayHasKey('hydrator_name', $resourceConfig);
        $this->assertArrayHasKey('controller_service_name', $resourceConfig);

        $this->assertEquals('barbaz', $resourceConfig['table_name']);
        $this->assertEquals($result->hydratorName, $resourceConfig['hydrator_name']);
        $this->assertEquals($result->controllerServiceName, $resourceConfig['controller_service_name']);
    }

    public function testCreateServiceWritesRestConfigurationWithEntityAndCollectionClass()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('api-tools-rest', $config);
        $this->assertArrayHasKey($result->controllerServiceName, $config['api-tools-rest']);

        $restConfig = $config['api-tools-rest'][$result->controllerServiceName];
        $this->assertArrayHasKey('entity_class', $restConfig);
        $this->assertArrayHasKey('collection_class', $restConfig);

        $this->assertEquals($result->entityClass, $restConfig['entity_class']);
        $this->assertEquals($result->collectionClass, $restConfig['collection_class']);
    }

    public function testCreateServiceWritesHalConfigurationWithHydrator()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        $this->assertArrayHasKey('api-tools-hal', $config);
        $this->assertArrayHasKey('metadata_map', $config['api-tools-hal']);
        $this->assertArrayHasKey($result->entityClass, $config['api-tools-hal']['metadata_map']);

        $halConfig = $config['api-tools-hal']['metadata_map'][$result->entityClass];
        $this->assertArrayHasKey('hydrator', $halConfig);

        $this->assertEquals($result->hydratorName, $halConfig['hydrator']);
    }

    public function testCreateServiceDoesNotCreateResourceClass()
    {
        $originalEntity = $this->getCreationPayload();
        $result         = $this->model->createService($originalEntity);
        $this->assertFalse(
            file_exists(__DIR__ . '/TestAsset/module/BarConf/src/BarConf/Rest/Barbaz/BarbazResource.php')
        );
    }

    public function testOnFetchWillRecastEntityToDbConnectedIfDbConnectedConfigurationExists()
    {
        $originalData = [
            'controller_service_name' => 'BarConf\Rest\Barbaz\Controller',
            'resource_class'          => 'BarConf\Rest\Barbaz\BarbazResource',
            'route_name'              => 'bar-conf.rest.barbaz',
            'route_match'             => '/api/barbaz',
            'entity_class'            => 'BarConf\Rest\Barbaz\BarbazEntity',
        ];
        $entity = new RestServiceEntity();
        $entity->exchangeArray($originalData);
        $config = [ 'api-tools' => ['db-connected' => [
            'BarConf\Rest\Barbaz\BarbazResource' => [
                'adapter_name'  => 'Db\Barbaz',
                'table_name'    => 'barbaz',
                'hydrator_name' => 'ObjectProperty',
            ],
        ]]];

        $event = new Event();
        $event->setParam('entity', $entity);
        $event->setParam('config', $config);
        $result = $this->model->onFetch($event);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\DbConnectedRestServiceEntity', $result);
        $asArray = $result->getArrayCopy();
        foreach ($originalData as $key => $value) {
            $this->assertArrayHasKey($key, $asArray);
            if ($key === 'resource_class') {
                $this->assertNull(
                    $asArray[$key],
                    sprintf("Failed asserting that resource_class is null\nEntity is: %s\n", var_export($asArray, 1))
                );
                continue;
            }
            $this->assertEquals(
                $value,
                $asArray[$key],
                sprintf("Failed testing key '%s'\nEntity is: %s\n", $key, var_export($asArray, 1))
            );
        }
        foreach ($config['api-tools']['db-connected']['BarConf\Rest\Barbaz\BarbazResource'] as $key => $value) {
            $this->assertArrayHasKey($key, $asArray);
            $this->assertEquals($value, $asArray[$key]);
        }
        $this->assertArrayHasKey('table_service', $asArray);
        $this->assertEquals($entity->resourceClass . '\\Table', $asArray['table_service']);
    }

    /**
     * @group 166
     */
    public function testOnFetchWillRetainResourceClassIfEventFetchFlagIsFalse()
    {
        $originalData = [
            'controller_service_name' => 'BarConf\Rest\Barbaz\Controller',
            'resource_class'          => 'BarConf\Rest\Barbaz\BarbazResource',
            'route_name'              => 'bar-conf.rest.barbaz',
            'route_match'             => '/api/barbaz',
            'entity_class'            => 'BarConf\Rest\Barbaz\BarbazEntity',
        ];
        $entity = new RestServiceEntity();
        $entity->exchangeArray($originalData);
        $config = [ 'api-tools' => ['db-connected' => [
            'BarConf\Rest\Barbaz\BarbazResource' => [
                'adapter_name'  => 'Db\Barbaz',
                'table_name'    => 'barbaz',
                'hydrator_name' => 'ObjectProperty',
            ],
        ]]];

        $event = new Event();
        $event->setParam('entity', $entity);
        $event->setParam('config', $config);
        $event->setParam('fetch', false);
        $result = $this->model->onFetch($event);

        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\DbConnectedRestServiceEntity', $result);
        $this->assertEquals('BarConf\Rest\Barbaz\BarbazResource', $result->resourceClass);
        $asArray = $result->getArrayCopy();
        $this->assertArrayHasKey('resource_class', $asArray);
        $this->assertEquals('BarConf\Rest\Barbaz\BarbazResource', $asArray['resource_class']);
    }

    public function testUpdateServiceReturnsUpdatedDbConnectedRestServiceEntity()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = [
            'table_service' => 'My\Custom\Table',
            'adapter_name'  => 'My\Db',
            'hydrator_name' => 'ClassMethods',
        ];
        $originalEntity->exchangeArray($newProps);
        $result = $this->model->updateService($originalEntity);

        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\DbConnectedRestServiceEntity', $result);
        $this->assertNotSame($originalEntity, $result);
        $this->assertEquals($newProps['table_service'], $result->tableService);
        $this->assertEquals($newProps['adapter_name'], $result->adapterName);
        $this->assertEquals($newProps['hydrator_name'], $result->hydratorName);
    }

    public function testUpdateServiceUpdatesDbConnectedConfiguration()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = [
            'table_service' => 'My\Custom\Table',
            'adapter_name'  => 'My\Db',
            'hydrator_name' => 'ClassMethods',
        ];
        $originalEntity->exchangeArray($newProps);
        $result = $this->model->updateService($originalEntity);

        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\DbConnectedRestServiceEntity', $result);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('api-tools', $config);
        $this->assertArrayHasKey('db-connected', $config['api-tools']);
        $this->assertArrayHasKey('BarConf\V1\Rest\Barbaz\BarbazResource', $config['api-tools']['db-connected']);

        $resourceConfig = $config['api-tools']['db-connected']['BarConf\V1\Rest\Barbaz\BarbazResource'];
        $this->assertArrayHasKey('adapter_name', $resourceConfig);
        $this->assertArrayHasKey('table_service', $resourceConfig);
        $this->assertArrayHasKey('table_name', $resourceConfig);
        $this->assertArrayHasKey('hydrator_name', $resourceConfig);

        $this->assertEquals($newProps['adapter_name'], $resourceConfig['adapter_name']);
        $this->assertEquals($newProps['table_service'], $resourceConfig['table_service']);
        $this->assertEquals('barbaz', $resourceConfig['table_name']);
        $this->assertEquals($newProps['hydrator_name'], $resourceConfig['hydrator_name']);
    }

    /**
     * @group 166
     */
    public function testUpdateServiceUpdatesEntityIdentifierNameAndHydrator()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);

        $newProps = [
            'entity_identifier_name' => 'id',
            'hydrator_name'          => 'Laminas\\Hydrator\\ClassMethods',
        ];
        $originalEntity->exchangeArray($newProps);
        $result = $this->model->updateService($originalEntity);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $this->assertArrayHasKey('api-tools', $config);
        $this->assertArrayHasKey('db-connected', $config['api-tools']);
        $this->assertArrayHasKey('BarConf\V1\Rest\Barbaz\BarbazResource', $config['api-tools']['db-connected']);

        $resourceConfig = $config['api-tools']['db-connected']['BarConf\V1\Rest\Barbaz\BarbazResource'];
        $this->assertEquals($newProps['entity_identifier_name'], $resourceConfig['entity_identifier_name']);
        $this->assertEquals($newProps['hydrator_name'], $resourceConfig['hydrator_name']);
    }

    public function testDeleteServiceRemovesDbConnectedConfigurationForEntity()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);
        $this->model->deleteService($originalEntity);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $barbazPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz';

        $this->assertTrue(file_exists($barbazPath));
        $this->assertArrayHasKey('api-tools', $config);
        $this->assertArrayHasKey('db-connected', $config['api-tools']);
        $this->assertArrayNotHasKey($originalEntity->resourceClass, $config['api-tools']['db-connected']);
    }

    public function testDeleteServiceRecursive()
    {
        $originalEntity = $this->getCreationPayload();
        $this->model->createService($originalEntity);
        $this->model->deleteService($originalEntity, true);

        $barbazPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Barbaz';
        $this->assertFalse(file_exists($barbazPath));
    }

    public function testCreateServiceWithUnderscoreInNameNormalizesClassNamesToCamelCase()
    {
        $originalEntity = $this->getCreationPayload();
        $originalEntity->exchangeArray(['table_name' => 'bar_baz']);

        $result = $this->model->createService($originalEntity);
        $this->assertSame($originalEntity, $result);

        $this->assertEquals('BarConf\V1\Rest\BarBaz\Controller', $result->controllerServiceName);
        $this->assertEquals('BarConf\V1\Rest\BarBaz\BarBazResource', $result->resourceClass);
        $this->assertEquals('BarConf\V1\Rest\BarBaz\BarBazEntity', $result->entityClass);
        $this->assertEquals('BarConf\V1\Rest\BarBaz\BarBazCollection', $result->collectionClass);
        $this->assertEquals('BarConf\V1\Rest\BarBaz\BarBazResource\Table', $result->tableService);
        $this->assertEquals('bar_baz', $result->tableName);
        $this->assertEquals('bar-conf.rest.bar-baz', $result->routeName);
    }
}
