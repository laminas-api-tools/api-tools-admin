<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use BarConf;
use BazConf;
use Laminas\ApiTools\Admin\Exception\RuntimeException;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\NewRestServiceEntity;
use Laminas\ApiTools\Admin\Model\RestServiceEntity;
use Laminas\ApiTools\Admin\Model\RestServiceModel;
use Laminas\ApiTools\Admin\Model\VersioningModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\ApiTools\Rest\Exception\CreationException;
use Laminas\Config\Writer\PhpArray;
use Laminas\Hydrator\ObjectPropertyHydrator;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Paginator\Paginator;
use LaminasTest\ApiTools\Admin\Model\TestAsset\Collection;
use LaminasTest\ApiTools\Admin\Model\TestAsset\Entity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_diff;
use function array_merge;
use function copy;
use function file_exists;
use function glob;
use function is_dir;
use function realpath;
use function rmdir;
use function scandir;
use function sprintf;
use function str_replace;
use function strpos;
use function unlink;
use function var_export;

class RestServiceModelTest extends TestCase
{
    /** @var string */
    private $module;
    /** @var ModuleEntity */
    private $moduleEntity;
    /** @var MockObject|ModuleManager */
    private $moduleManager;
    /** @var ModulePathSpec */
    private $modules;
    /** @var ResourceFactory */
    private $resource;
    /** @var RestServiceModel */
    private $codeRest;

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
        $pathSpec = empty($this->modules) ? 'psr-0' : $this->modules->getPathSpec();

        $modulePath = [
            'psr-0' => '%s/src/%s/V*',
            'psr-4' => '%s/src/V*',
        ];

        $basePath   = sprintf('%s/TestAsset/module/%s', __DIR__, $this->module);
        $configPath = $basePath . '/config';
        foreach (glob(sprintf($modulePath[$pathSpec], $basePath, $this->module)) as $dir) {
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
            'BazConf' => new BazConf\Module(),
        ];

        $this->moduleEntity  = new ModuleEntity($this->module, [], [], false);
        $this->moduleManager = $this->getMockBuilder(ModuleManager::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $writer         = new PhpArray();
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->modules  = new ModulePathSpec($moduleUtils);
        $this->resource = new ResourceFactory($moduleUtils, $writer);
        $this->codeRest = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BarConf')
        );
    }

    public function tearDown(): void
    {
        $this->cleanUpAssets();
    }

    public function getCreationPayload(): NewRestServiceEntity
    {
        $payload = new NewRestServiceEntity();
        $payload->exchangeArray([
            'service_name'               => 'foo',
            'route_match'                => '/api/foo',
            'route_identifier_name'      => 'foo_id',
            'collection_name'            => 'foo',
            'entity_http_methods'        => ['GET', 'PATCH'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_query_whitelist' => ['sort', 'filter'],
            'page_size'                  => 10,
            'page_size_param'            => 'p',
            'selector'                   => 'HalJson',
            'accept_whitelist'           => ['application/json', 'application/*+json'],
            'content_type_whitelist'     => ['application/json'],
            'hydrator_name'              => ObjectPropertyHydrator::class,
        ]);

        return $payload;
    }

    public function testRejectInvalidRestServiceName1(): void
    {
        $this->expectException(CreationException::class);
        $restServiceEntity = new NewRestServiceEntity();
        $restServiceEntity->exchangeArray(['servicename' => 'Foo Bar']);
        $this->codeRest->createService($restServiceEntity);
    }

    public function testRejectInvalidRestServiceName2(): void
    {
        $this->expectException(CreationException::class);
        $restServiceEntity = new NewRestServiceEntity();
        $restServiceEntity->exchangeArray(['serivcename' => 'Foo:Bar']);
        $this->codeRest->createService($restServiceEntity);
    }

    public function testRejectInvalidRestServiceName3(): void
    {
        $this->expectException(CreationException::class);
        $restServiceEntity = new NewRestServiceEntity();
        $restServiceEntity->exchangeArray(['servicename' => 'Foo/Bar']);
        $this->codeRest->createService($restServiceEntity);
    }

    public function testCanCreateControllerServiceNameFromServiceNameSpace(): void
    {
        self::assertEquals(
            'BarConf\V1\Rest\Foo\Bar\Baz\Controller',
            $this->codeRest->createControllerServiceName('Foo\Bar\Baz')
        );
    }

    public function testCanCreateControllerServiceNameFromServiceName(): void
    {
        self::assertEquals('BarConf\V1\Rest\Foo\Controller', $this->codeRest->createControllerServiceName('Foo'));
    }

    public function testCreateResourceClassReturnsClassNameCreated(): void
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');
        self::assertEquals('BarConf\V1\Rest\Foo\FooResource', $resourceClass);
    }

    public function testCreateResourceClassCreatesClassFileWithNamedResourceClass(): void
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $resourceClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo/' . $className . '.php';
        self::assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($resourceClass);
        self::assertInstanceOf('ReflectionClass', $r);
        $parent = $r->getParentClass();
        self::assertEquals(AbstractResourceListener::class, $parent->getName());
    }

    /**
     * @group feature/psr4
     */
    public function testCreateResourceClassCreatesClassFileWithNamedResourceClassPSR4(): void
    {
        $this->module       = 'BazConf';
        $this->moduleEntity = new ModuleEntity($this->module);
        $moduleUtils        = new ModuleUtils($this->moduleManager);
        $this->modules      = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRest     = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BazConf')
        );

        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $resourceClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BazConf/src/V1/Rest/Foo/' . $className . '.php';
        self::assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($resourceClass);
        self::assertInstanceOf('ReflectionClass', $r);
        $parent = $r->getParentClass();
        self::assertEquals(AbstractResourceListener::class, $parent->getName());
    }

    public function testCreateResourceClassAddsInvokableToConfiguration(): void
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('service_manager', $config);
        self::assertArrayHasKey('factories', $config['service_manager']);
        self::assertArrayHasKey($resourceClass, $config['service_manager']['factories']);
        self::assertEquals($resourceClass . 'Factory', $config['service_manager']['factories'][$resourceClass]);
    }

    public function testCreateResourceClassCreateFactory(): void
    {
        $resourceClass = $this->codeRest->createResourceClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $resourceClass . 'Factory');
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo/' . $className . '.php';
        self::assertTrue(file_exists($path));
    }

    public function testCreateEntityClassReturnsClassNameCreated(): void
    {
        $entityClass = $this->codeRest->createEntityClass('Foo');
        self::assertEquals('BarConf\V1\Rest\Foo\FooEntity', $entityClass);
    }

    public function testCreateEntityClassCreatesClassFileWithNamedEntityClass(): void
    {
        $entityClass = $this->codeRest->createEntityClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $entityClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo/' . $className . '.php';
        self::assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($entityClass);
        self::assertInstanceOf('ReflectionClass', $r);
        self::assertFalse($r->getParentClass());
    }

    public function testCreateCollectionClassReturnsClassNameCreated(): void
    {
        $collectionClass = $this->codeRest->createCollectionClass('Foo');
        self::assertEquals('BarConf\V1\Rest\Foo\FooCollection', $collectionClass);
    }

    public function testCreateCollectionClassCreatesClassFileWithNamedCollectionClass(): void
    {
        $collectionClass = $this->codeRest->createCollectionClass('Foo');

        $className = str_replace($this->module . '\\V1\\Rest\\Foo\\', '', $collectionClass);
        $path      = realpath(__DIR__) . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo/' . $className . '.php';
        self::assertTrue(file_exists($path));

        require_once $path;

        $r = new ReflectionClass($collectionClass);
        self::assertInstanceOf('ReflectionClass', $r);
        $parent = $r->getParentClass();
        self::assertEquals(Paginator::class, $parent->getName());
    }

    public function testCreateRouteReturnsNewRouteName(): void
    {
        $routeName = $this->codeRest->createRoute('FooBar', '/foo-bar', 'foo_bar_id', 'BarConf\Rest\FooBar\Controller');
        self::assertEquals('bar-conf.rest.foo-bar', $routeName);
    }

    public function testCreateRouteWritesRouteConfiguration(): void
    {
        $routeName = $this->codeRest->createRoute('FooBar', '/foo-bar', 'foo_bar_id', 'BarConf\Rest\FooBar\Controller');

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('router', $config);
        self::assertArrayHasKey('routes', $config['router']);
        $routes = $config['router']['routes'];

        self::assertArrayHasKey($routeName, $routes);
        $expected = [
            'type'    => 'Segment',
            'options' => [
                'route'    => '/foo-bar[/:foo_bar_id]',
                'defaults' => [
                    'controller' => 'BarConf\Rest\FooBar\Controller',
                ],
            ],
        ];
        self::assertEquals($expected, $routes[$routeName]);
    }

    public function testCreateRouteWritesVersioningConfiguration(): void
    {
        $routeName = $this->codeRest->createRoute('FooBar', '/foo-bar', 'foo_bar_id', 'BarConf\Rest\FooBar\Controller');

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('router', $config);
        self::assertArrayHasKey('routes', $config['router']);
        $routes = $config['api-tools-versioning']['uri'];

        self::assertContains($routeName, $routes);
    }

    public function testCreateRestConfigWritesRestConfiguration(): void
    {
        $details = $this->getCreationPayload();
        $details->exchangeArray([
            'entity_class'     => 'BarConf\Rest\Foo\FooEntity',
            'collection_class' => 'BarConf\Rest\Foo\FooCollection',
        ]);
        $this->codeRest->createRestConfig(
            $details,
            'BarConf\Rest\Foo\Controller',
            'BarConf\Rest\Foo\FooResource',
            'bar-conf.rest.foo'
        );
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        self::assertArrayHasKey('api-tools-rest', $config);
        self::assertArrayHasKey('BarConf\Rest\Foo\Controller', $config['api-tools-rest']);
        $config = $config['api-tools-rest']['BarConf\Rest\Foo\Controller'];

        $expected = [
            'service_name'               => 'foo',
            'listener'                   => 'BarConf\Rest\Foo\FooResource',
            'route_name'                 => 'bar-conf.rest.foo',
            'route_identifier_name'      => $details->routeIdentifierName,
            'collection_name'            => $details->collectionName,
            'entity_http_methods'        => $details->entityHttpMethods,
            'collection_http_methods'    => $details->collectionHttpMethods,
            'collection_query_whitelist' => $details->collectionQueryWhitelist,
            'page_size'                  => $details->pageSize,
            'page_size_param'            => $details->pageSizeParam,
            'entity_class'               => $details->entityClass,
            'collection_class'           => $details->collectionClass,
        ];
        self::assertEquals($expected, $config);
    }

    public function testCreateContentNegotiationConfigWritesContentNegotiationConfiguration(): void
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createContentNegotiationConfig($details, 'BarConf\Rest\Foo\Controller');
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        self::assertArrayHasKey('api-tools-content-negotiation', $config);
        $config = $config['api-tools-content-negotiation'];

        self::assertArrayHasKey('controllers', $config);
        self::assertEquals([
            'BarConf\Rest\Foo\Controller' => $details->selector,
        ], $config['controllers']);

        self::assertArrayHasKey('accept_whitelist', $config);
        self::assertEquals([
            'BarConf\Rest\Foo\Controller' => $details->acceptWhitelist,
        ], $config['accept_whitelist'], var_export($config, true));

        self::assertArrayHasKey('content_type_whitelist', $config);
        self::assertEquals([
            'BarConf\Rest\Foo\Controller' => $details->contentTypeWhitelist,
        ], $config['content_type_whitelist'], var_export($config, true));
    }

    public function testCreateHalConfigWritesHalConfiguration(): void
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createHalConfig(
            $details,
            'BarConf\Rest\Foo\FooEntity',
            'BarConf\Rest\Foo\FooCollection',
            'bar-conf.rest.foo'
        );
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        self::assertArrayHasKey('api-tools-hal', $config);
        self::assertArrayHasKey('metadata_map', $config['api-tools-hal']);
        $config = $config['api-tools-hal']['metadata_map'];

        self::assertArrayHasKey('BarConf\Rest\Foo\FooEntity', $config);
        self::assertEquals([
            'route_identifier_name'  => $details->routeIdentifierName,
            'route_name'             => 'bar-conf.rest.foo',
            'hydrator'               => ObjectPropertyHydrator::class,
            'entity_identifier_name' => 'id',
        ], $config['BarConf\Rest\Foo\FooEntity']);

        self::assertArrayHasKey('BarConf\Rest\Foo\FooCollection', $config);
        self::assertEquals([
            'route_identifier_name'  => $details->routeIdentifierName,
            'route_name'             => 'bar-conf.rest.foo',
            'is_collection'          => true,
            'entity_identifier_name' => 'id',
        ], $config['BarConf\Rest\Foo\FooCollection']);
    }

    public function testCreateServiceReturnsRestServiceEntityOnSuccess(): void
    {
        $details = $this->getCreationPayload();
        $result  = $this->codeRest->createService($details);
        self::assertInstanceOf(RestServiceEntity::class, $result);

        self::assertEquals('BarConf', $result->module);
        self::assertEquals('foo', $result->serviceName);
        self::assertEquals('BarConf\V1\Rest\Foo\Controller', $result->controllerServiceName);
        self::assertEquals('BarConf\V1\Rest\Foo\FooResource', $result->resourceClass);
        self::assertEquals('BarConf\V1\Rest\Foo\FooEntity', $result->entityClass);
        self::assertEquals('BarConf\V1\Rest\Foo\FooCollection', $result->collectionClass);
        self::assertEquals('bar-conf.rest.foo', $result->routeName);
        self::assertEquals(
            ['application/vnd.bar-conf.v1+json', 'application/hal+json', 'application/json'],
            $result->acceptWhitelist
        );
        self::assertEquals(
            ['application/vnd.bar-conf.v1+json', 'application/json'],
            $result->contentTypeWhitelist
        );
    }

    public function testCreateServiceUsesDefaultContentNegotiation(): void
    {
        $payload = new NewRestServiceEntity();
        $payload->exchangeArray([
            'service_name' => 'foo',
        ]);
        $result = $this->codeRest->createService($payload);
        self::assertInstanceOf(RestServiceEntity::class, $result);
        self::assertEquals(
            ['application/vnd.bar-conf.v1+json', 'application/hal+json', 'application/json'],
            $result->acceptWhitelist
        );
        self::assertEquals(
            ['application/vnd.bar-conf.v1+json', 'application/json'],
            $result->contentTypeWhitelist
        );
    }

    public function testCanFetchServiceAfterCreation(): void
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createService($details);

        $service = $this->codeRest->fetch('BarConf\V1\Rest\Foo\Controller');
        self::assertInstanceOf(RestServiceEntity::class, $service);

        self::assertEquals('BarConf', $service->module);
        self::assertEquals('foo', $service->serviceName);
        self::assertEquals('BarConf\V1\Rest\Foo\Controller', $service->controllerServiceName);
        self::assertEquals('BarConf\V1\Rest\Foo\FooResource', $service->resourceClass);
        self::assertEquals('BarConf\V1\Rest\Foo\FooEntity', $service->entityClass);
        self::assertEquals('BarConf\V1\Rest\Foo\FooCollection', $service->collectionClass);
        self::assertEquals('bar-conf.rest.foo', $service->routeName);
        self::assertEquals('/api/foo[/:foo_id]', $service->routeMatch);
        self::assertEquals(ObjectPropertyHydrator::class, $service->hydratorName);
    }

    public function testFetchServiceUsesEntityAndCollectionClassesDiscoveredInRestConfiguration(): void
    {
        $details = $this->getCreationPayload();
        $details->exchangeArray([
            'entity_class'     => Entity::class,
            'collection_class' => Collection::class,
        ]);
        $this->codeRest->createService($details);

        $service = $this->codeRest->fetch('BarConf\V1\Rest\Foo\Controller');
        self::assertInstanceOf(RestServiceEntity::class, $service);

        self::assertEquals(Entity::class, $service->entityClass);
        self::assertEquals(Collection::class, $service->collectionClass);
    }

    public function testCanUpdateRouteForExistingService(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $patch = new RestServiceEntity();
        $patch->exchangeArray([
            'controller_service_name' => 'BarConf\Rest\Foo\Controller',
            'route_match'             => '/api/bar/foo',
        ]);

        $this->codeRest->updateRoute($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('router', $config);
        self::assertArrayHasKey('routes', $config['router']);
        self::assertArrayHasKey($original->routeName, $config['router']['routes']);
        $routeConfig = $config['router']['routes'][$original->routeName];
        self::assertArrayHasKey('options', $routeConfig);
        self::assertArrayHasKey('route', $routeConfig['options']);
        self::assertEquals('/api/bar/foo', $routeConfig['options']['route']);
    }

    public function testCanUpdateRestConfigForExistingService(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = [
            'page_size'                  => 30,
            'page_size_param'            => 'r',
            'collection_query_whitelist' => ['f', 's'],
            'collection_http_methods'    => ['GET'],
            'entity_http_methods'        => ['GET'],
            'entity_class'               => Entity::class,
            'collection_class'           => Collection::class,
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateRestConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools-rest', $config);
        self::assertArrayHasKey($original->controllerServiceName, $config['api-tools-rest']);
        $test = $config['api-tools-rest'][$original->controllerServiceName];

        foreach ($options as $key => $value) {
            self::assertEquals($value, $test[$key]);
        }
    }

    public function testCanUpdateContentNegotiationConfigForExistingService(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = [
            'selector'               => 'Json',
            'accept_whitelist'       => ['application/json'],
            'content_type_whitelist' => ['application/json'],
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateContentNegotiationConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools-content-negotiation', $config);
        $config = $config['api-tools-content-negotiation'];

        self::assertArrayHasKey('controllers', $config);
        self::assertArrayHasKey($original->controllerServiceName, $config['controllers']);
        self::assertEquals($options['selector'], $config['controllers'][$original->controllerServiceName]);

        self::assertArrayHasKey('accept_whitelist', $config);
        self::assertArrayHasKey($original->controllerServiceName, $config['accept_whitelist']);
        self::assertEquals(
            $options['accept_whitelist'],
            $config['accept_whitelist'][$original->controllerServiceName]
        );

        self::assertArrayHasKey('content_type_whitelist', $config);
        self::assertArrayHasKey($original->controllerServiceName, $config['content_type_whitelist']);
        self::assertEquals(
            $options['content_type_whitelist'],
            $config['content_type_whitelist'][$original->controllerServiceName]
        );
    }

    public function testCanUpdateHalConfigForExistingService(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = [
            'hydrator_name'         => ReflectionHydrator::class,
            'route_identifier_name' => 'custom_foo_id',
            'route_name'            => 'my/custom/route',
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateHalConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools-hal', $config);
        self::assertArrayHasKey('metadata_map', $config['api-tools-hal']);
        $config = $config['api-tools-hal']['metadata_map'];

        $entityName     = $original->entityClass;
        $collectionName = $original->collectionClass;
        self::assertArrayHasKey($entityName, $config);
        self::assertArrayHasKey($collectionName, $config);

        $entityConfig     = $config[$entityName];
        $collectionConfig = $config[$collectionName];

        self::assertArrayHasKey('route_identifier_name', $entityConfig);
        self::assertEquals($options['route_identifier_name'], $entityConfig['route_identifier_name']);
        self::assertArrayHasKey('route_identifier_name', $collectionConfig);
        self::assertEquals($options['route_identifier_name'], $collectionConfig['route_identifier_name']);

        self::assertArrayHasKey('route_name', $entityConfig);
        self::assertEquals($options['route_name'], $entityConfig['route_name']);
        self::assertArrayHasKey('route_name', $collectionConfig);
        self::assertEquals($options['route_name'], $collectionConfig['route_name']);

        self::assertArrayHasKey('hydrator', $entityConfig);
        self::assertEquals($options['hydrator_name'], $entityConfig['hydrator']);
        self::assertArrayNotHasKey('hydrator', $collectionConfig);
    }

    public function testCanUpdateHalConfigForExistingServiceAndProvideNewEntityAndCollectionClasses(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = [
            'entity_class'          => Entity::class,
            'collection_class'      => Collection::class,
            'hydrator_name'         => ReflectionHydrator::class,
            'route_identifier_name' => 'custom_foo_id',
            'route_name'            => 'my/custom/route',
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateHalConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools-hal', $config);
        self::assertArrayHasKey('metadata_map', $config['api-tools-hal']);
        $config = $config['api-tools-hal']['metadata_map'];

        $entityName     = $patch->entityClass;
        $collectionName = $patch->collectionClass;

        self::assertArrayHasKey($entityName, $config);
        self::assertArrayHasKey($collectionName, $config);

        $entityConfig     = $config[$entityName];
        $collectionConfig = $config[$collectionName];

        self::assertArrayHasKey('route_identifier_name', $entityConfig);
        self::assertEquals($options['route_identifier_name'], $entityConfig['route_identifier_name']);
        self::assertArrayHasKey('route_identifier_name', $collectionConfig);
        self::assertEquals($options['route_identifier_name'], $collectionConfig['route_identifier_name']);

        self::assertArrayHasKey('route_name', $entityConfig);
        self::assertEquals($options['route_name'], $entityConfig['route_name']);
        self::assertArrayHasKey('route_name', $collectionConfig);
        self::assertEquals($options['route_name'], $collectionConfig['route_name']);

        self::assertArrayHasKey('hydrator', $entityConfig);
        self::assertEquals($options['hydrator_name'], $entityConfig['hydrator']);
        self::assertArrayNotHasKey('hydrator', $collectionConfig);
    }

    public function testUpdateServiceReturnsUpdatedRepresentation(): void
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createService($details);

        $updates = [
            'route_match'                => '/api/bar/foo',
            'page_size'                  => 30,
            'page_size_param'            => 'r',
            'collection_query_whitelist' => ['f', 's'],
            'collection_http_methods'    => ['GET'],
            'entity_http_methods'        => ['GET'],
            'selector'                   => 'Json',
            'accept_whitelist'           => ['application/json'],
            'content_type_whitelist'     => ['application/json'],
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray(array_merge([
            'controller_service_name' => 'BarConf\V1\Rest\Foo\Controller',
        ], $updates));

        $updated = $this->codeRest->updateService($patch);
        self::assertInstanceOf(RestServiceEntity::class, $updated);

        $values = $updated->getArrayCopy();

        foreach ($updates as $key => $value) {
            self::assertArrayHasKey($key, $values);
            if ($key === 'route_match') {
                self::assertEquals(0, strpos($value, $values[$key]));
                continue;
            }
            self::assertEquals($value, $values[$key]);
        }
    }

    public function testFetchListenersCanReturnAlternateEntities(): void
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createService($details);

        $alternateEntity = new RestServiceEntity();
        $this->codeRest->getEventManager()->attach('fetch', function ($e) use ($alternateEntity) {
            return $alternateEntity;
        });

        $result = $this->codeRest->fetch('BarConf\V1\Rest\Foo\Controller');
        self::assertSame($alternateEntity, $result);
    }

    public function testCanDeleteAService(): void
    {
        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        self::assertTrue($this->codeRest->deleteService($service->controllerServiceName));

        $fooPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo';
        self::assertTrue(file_exists($fooPath));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('find');
        $this->expectExceptionCode(404);
        $this->codeRest->fetch($service->controllerServiceName);
    }

    /**
     * @group feature/psr4
     */
    public function testCanDeleteAServicePSR4(): void
    {
        $this->module       = 'BazConf';
        $this->moduleEntity = new ModuleEntity($this->module);
        $moduleUtils        = new ModuleUtils($this->moduleManager);
        $this->modules      = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRest     = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BazConf')
        );

        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        self::assertTrue($this->codeRest->deleteService($service->controllerServiceName));

        $fooPath = __DIR__ . '/TestAsset/module/BazConf/src/V1/Rest/Foo';
        self::assertTrue(file_exists($fooPath));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('find');
        $this->expectExceptionCode(404);
        $this->codeRest->fetch($service->controllerServiceName);
    }

    public function testCanDeleteAServiceRecursive(): void
    {
        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        self::assertTrue($this->codeRest->deleteService($service->controllerServiceName, true));

        $fooPath = __DIR__ . '/TestAsset/module/BarConf/src/BarConf/V1/Rest/Foo';
        self::assertFalse(file_exists($fooPath));
    }

    /**
     * @group feature/psr4
     */
    public function testCanDeleteAServiceRecursivePSR4(): void
    {
        $this->module       = 'BazConf';
        $this->moduleEntity = new ModuleEntity($this->module);
        $moduleUtils        = new ModuleUtils($this->moduleManager);
        $this->modules      = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRest     = new RestServiceModel(
            $this->moduleEntity,
            $this->modules,
            $this->resource->factory('BazConf')
        );

        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        self::assertTrue($this->codeRest->deleteService($service->controllerServiceName, true));

        $fooPath = __DIR__ . '/TestAsset/module/BazConf/src/V1/Rest/Foo';
        self::assertFalse(file_exists($fooPath));
    }

    /**
     * @depends testCanDeleteAService
     */
    public function testDeletingAServiceRemovesAllRelatedConfigKeys(): void
    {
        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        self::assertTrue($this->codeRest->deleteService($service->controllerServiceName));
        $path   = __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        $config = include $path;
        self::assertIsArray($config);
        self::assertIsArray($config['api-tools-rest']);
        self::assertIsArray($config['api-tools-versioning']);
        self::assertIsArray($config['router']['routes']);
        self::assertIsArray($config['api-tools-content-negotiation']);
        self::assertIsArray($config['service_manager']);
        self::assertIsArray($config['api-tools-hal']);

        // phpcs:disable
        self::assertArrayNotHasKey('BarConf\V1\Rest\Foo\Controller', $config['api-tools-rest'], 'REST entry not deleted');
        self::assertArrayNotHasKey('bar-conf.rest.foo', $config['router']['routes'], 'Route not deleted');
        self::assertNotContains('bar-conf.rest.foo', $config['api-tools-versioning']['uri'], 'Versioning not deleted');
        self::assertArrayNotHasKey('BarConf\\V1\\Rest\\Foo\\Controller', $config['api-tools-content-negotiation']['controllers'], 'Content Negotiation controllers entry not deleted');
        self::assertArrayNotHasKey('BarConf\V1\Rest\Foo\Controller', $config['api-tools-content-negotiation']['accept_whitelist'], 'Content Negotiation accept whitelist entry not deleted');
        self::assertArrayNotHasKey('BarConf\V1\Rest\Foo\Controller', $config['api-tools-content-negotiation']['content_type_whitelist'], 'Content Negotiation content-type whitelist entry not deleted');
        // phpcs:enable
        foreach ($config['service_manager'] as $services) {
            self::assertArrayNotHasKey('BarConf\V1\Rest\Foo\FooResource', $services, 'Service entry not deleted');
        }
        self::assertArrayNotHasKey(
            'BarConf\V1\Rest\Foo\FooEntity',
            $config['api-tools-hal']['metadata_map'],
            'HAL entity not deleted'
        );
        self::assertArrayNotHasKey(
            'BarConf\V1\Rest\Foo\FooCollection',
            $config['api-tools-hal']['metadata_map'],
            'HAL collection not deleted'
        );
    }

    /**
     * @depends testDeletingAServiceRemovesAllRelatedConfigKeys
     */
    public function testDeletingNewerVersionOfServiceDoesNotRemoveRouteOrVersioningConfiguration(): void
    {
        $details = $this->getCreationPayload();
        $service = $this->codeRest->createService($details);

        $path            = __DIR__ . '/TestAsset/module/BarConf';
        $versioningModel = new VersioningModel($this->resource->factory('BarConf'));
        self::assertTrue($versioningModel->createVersion('BarConf', 2));

        $serviceName = str_replace('1', '2', $service->controllerServiceName);
        $this->codeRest->fetch($serviceName);
        self::assertTrue($this->codeRest->deleteService($serviceName));

        $config = include $path . '/config/module.config.php';
        self::assertIsArray($config);
        self::assertIsArray($config['api-tools-versioning']);
        self::assertIsArray($config['router']['routes']);

        self::assertArrayHasKey('BarConf\V1\Rest\Foo\Controller', $config['api-tools-rest']);
        self::assertArrayNotHasKey('BarConf\V2\Rest\Foo\Controller', $config['api-tools-rest']);
        self::assertArrayHasKey('bar-conf.rest.foo', $config['router']['routes'], 'Route DELETED');
        self::assertContains('bar-conf.rest.foo', $config['api-tools-versioning']['uri'], 'Versioning DELETED');
    }

    /**
     * @group skeleton-37
     */
    public function testUpdateHalConfigShouldNotRemoveIsCollectionKey(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = [
            'hydrator_name'         => ReflectionHydrator::class,
            'route_identifier_name' => 'custom_foo_id',
            'route_name'            => 'my/custom/route',
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateHalConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools-hal', $config);
        self::assertArrayHasKey('metadata_map', $config['api-tools-hal']);
        $config = $config['api-tools-hal']['metadata_map'];

        $collectionName = $original->collectionClass;
        self::assertArrayHasKey($collectionName, $config);

        $collectionConfig = $config[$collectionName];
        self::assertArrayHasKey('is_collection', $collectionConfig);
        self::assertTrue($collectionConfig['is_collection']);
    }

    /**
     * @group 76
     */
    public function testUpdateHalConfigShouldKeepExistingKeysIntact(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = [
            'hydrator_name'          => ReflectionHydrator::class,
            'entity_identifier_name' => 'custom_foo_id',
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateHalConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools-hal', $config);
        self::assertArrayHasKey('metadata_map', $config['api-tools-hal']);
        $config = $config['api-tools-hal']['metadata_map'];

        $entityName     = $original->entityClass;
        $collectionName = $original->collectionClass;
        self::assertArrayHasKey($entityName, $config);
        self::assertArrayHasKey($collectionName, $config);

        $entityConfig = $config[$entityName];
        self::assertArrayHasKey('entity_identifier_name', $entityConfig);
        self::assertArrayHasKey('route_identifier_name', $entityConfig);
        self::assertArrayHasKey('route_name', $entityConfig);
        self::assertEquals($options['entity_identifier_name'], $entityConfig['entity_identifier_name']);
        self::assertEquals($original->routeIdentifierName, $entityConfig['route_identifier_name']);
        self::assertEquals($original->routeName, $entityConfig['route_name']);

        self::assertArrayHasKey('entity_identifier_name', $entityConfig);
        self::assertArrayHasKey('route_identifier_name', $entityConfig);
        self::assertArrayHasKey('route_name', $entityConfig);
        self::assertEquals($options['entity_identifier_name'], $entityConfig['entity_identifier_name']);
        self::assertEquals($original->routeIdentifierName, $entityConfig['route_identifier_name']);
        self::assertEquals($original->routeName, $entityConfig['route_name']);
    }

    /**
     * @group 72
     */
    public function testCanRemoveAllHttpVerbsWhenUpdating(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = [
            'collection_http_methods' => [],
            'entity_http_methods'     => [],
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateRestConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools-rest', $config);
        self::assertArrayHasKey($original->controllerServiceName, $config['api-tools-rest']);
        $test = $config['api-tools-rest'][$original->controllerServiceName];

        self::assertEquals([], $test['collection_http_methods']);
        self::assertEquals([], $test['entity_http_methods']);
    }

    /**
     * @group 170
     */
    public function testUpdateRestWillUpdateCollectionName(): void
    {
        $details  = $this->getCreationPayload();
        $original = $this->codeRest->createService($details);

        $options = [
            'collection_name' => 'foo_bars',
        ];
        $patch   = new RestServiceEntity();
        $patch->exchangeArray($options);

        $this->codeRest->updateRestConfig($original, $patch);

        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
        self::assertArrayHasKey('api-tools-rest', $config);
        self::assertArrayHasKey($original->controllerServiceName, $config['api-tools-rest']);
        $test = $config['api-tools-rest'][$original->controllerServiceName];

        foreach ($options as $key => $value) {
            self::assertEquals($value, $test[$key]);
        }
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin-ui/issues/23
     */
    public function testServiceExistsThrowExceptionAndLeaveConfigAsIs(): void
    {
        $details = $this->getCreationPayload();
        $result  = $this->codeRest->createService($details);
        self::assertInstanceOf(RestServiceEntity::class, $result);
        $config = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';

        // create a second service with the same name and data
        try {
            $result = $this->codeRest->createService($details);
            self::fail('Should not have created service due to duplicate existing already');
        } catch (RuntimeException $e) {
            $config2 = include __DIR__ . '/TestAsset/module/BarConf/config/module.config.php';
            // check the configuration is unchanged
            self::assertEquals($config, $config2);
        }
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin/issues/49
     */
    public function testCreateServiceWithUrlAlreadyExist(): void
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createService($details);

        // Create a new REST entity with same URL match
        $payload                 = $details->getArrayCopy();
        $payload['service_name'] = 'bar';
        $restService             = new NewRestServiceEntity();
        $restService->exchangeArray($payload);

        $this->expectException(RuntimeException::class);
        $this->codeRest->createService($restService);
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin/issues/49
     */
    public function testUpdateServiceWithUrlAlreadyExist(): void
    {
        $details = $this->getCreationPayload();
        $this->codeRest->createService($details);

        // Create a new REST entity
        $payload                          = $details->getArrayCopy();
        $payload['service_name']          = 'bar';
        $payload['route_match']           = '/api/bar';
        $payload['route_identifier_name'] = 'bar_id';
        $payload['collection_name']       = 'bar';
        $restService                      = new NewRestServiceEntity();
        $restService->exchangeArray($payload);

        $second = $this->codeRest->createService($restService);

        $payload = $second->getArrayCopy();
        // Update the second REST service with same URL of the first one
        $payload['route_match'] = '/api/foo';
        $patch                  = new NewRestServiceEntity();
        $patch->exchangeArray($payload);

        $this->expectException(RuntimeException::class);
        $this->codeRest->updateService($patch);
    }
}
