<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use BazConf;
use FooConf;
use Laminas\ApiTools\Admin\Exception\RuntimeException;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\RpcServiceEntity;
use Laminas\ApiTools\Admin\Model\RpcServiceModel;
use Laminas\ApiTools\Admin\Model\VersioningModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\ApiTools\Rest\Exception\CreationException;
use Laminas\Config\Writer\PhpArray;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Controller\AbstractActionController;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_diff;
use function class_exists;
use function copy;
use function file_exists;
use function file_get_contents;
use function glob;
use function is_dir;
use function lcfirst;
use function mkdir;
use function realpath;
use function rmdir;
use function scandir;
use function sprintf;
use function str_replace;
use function unlink;

class RpcServiceModelTest extends TestCase
{
    /** @var string */
    private $module;
    /** @var ModuleEntity */
    private $moduleEntity;
    /** @var MockObject|ModuleManager */
    private $moduleManager;
    /** @var PhpArray */
    private $writer;
    /** @var ModulePathSpec */
    private $modulePathSpec;
    /** @var ResourceFactory */
    private $resource;
    /** @var RpcServiceModel */
    private $codeRpc;

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
        $pathSpec = empty($this->modulePathSpec) ? 'psr-0' : $this->modulePathSpec->getPathSpec();

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
        $this->module = 'FooConf';
        $this->cleanUpAssets();

        $modules = [
            'FooConf' => new FooConf\Module(),
            'BazConf' => new BazConf\Module(),
        ];

        $this->moduleEntity  = new ModuleEntity($this->module);
        $this->moduleManager = $this->getMockBuilder(ModuleManager::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer         = new PhpArray();
        $moduleUtils          = new ModuleUtils($this->moduleManager);
        $this->modulePathSpec = new ModulePathSpec($moduleUtils);
        $this->resource       = new ResourceFactory($moduleUtils, $this->writer);
        $this->codeRpc        = new RpcServiceModel(
            $this->moduleEntity,
            $this->modulePathSpec,
            $this->resource->factory($this->module)
        );
    }

    public function tearDown(): void
    {
        $this->cleanUpAssets();
    }

    public function testRejectSpacesInRpcServiceName1(): void
    {
        /**
         * @todo define exception in Rpc namespace
         */
        $this->expectException(CreationException::class);
        $this->codeRpc->createService('Foo Bar', 'route', []);
    }

    public function testRejectSpacesInRpcServiceName2(): void
    {
        /**
         * @todo define exception in Rpc namespace
        */
        $this->expectException(CreationException::class);
        $this->codeRpc->createService('Foo:Bar', 'route', []);
    }

    public function testRejectSpacesInRpcServiceName3(): void
    {
        /**
         * @todo define exception in Rpc namespace
        */
        $this->expectException(CreationException::class);
        $this->codeRpc->createService('Foo/Bar', 'route', []);
    }

    /**
     * @group createController
     */
    public function testCanCreateControllerServiceNameFromResourceNameSpace(): void
    {
        $this->markTestSkipped('Invalid use case');

        /**
         * @todo is this the expected behavior?
        */
        self::assertEquals(
            'FooConf\V1\Rpc\Baz\Bat\Foo\Baz\Bat\FooController',
            $this->codeRpc->createController('Baz\Bat\Foo')->class
        );
    }

    public function testCreateControllerRpc(): void
    {
        $serviceName   = 'Bar';
        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src/%s', __DIR__, $this->module, $this->module);
        if (! is_dir($moduleSrcPath)) {
            mkdir($moduleSrcPath, 0775, true);
        }

        $result = $this->codeRpc->createController($serviceName);
        self::assertInstanceOf('stdClass', $result);
        self::assertObjectHasProperty('class', $result);
        self::assertObjectHasProperty('file', $result);
        self::assertObjectHasProperty('service', $result);

        $className         = sprintf("%s\\V1\\Rpc\\%s\\%sController", $this->module, $serviceName, $serviceName);
        $fileName          = sprintf(
            "%s/TestAsset/module/%s/src/%s/V1/Rpc/%s/%sController.php",
            __DIR__,
            $this->module,
            $this->module,
            $serviceName,
            $serviceName
        );
        $controllerService = sprintf("%s\\V1\\Rpc\\%s\\Controller", $this->module, $serviceName);

        self::assertEquals($className, $result->class);
        self::assertEquals(realpath($fileName), realpath($result->file));
        self::assertEquals($controllerService, $result->service);

        if (! class_exists($className)) {
            require_once $fileName;
        }
        $controllerClass = new ReflectionClass($className);
        self::assertTrue($controllerClass->isSubclassOf(AbstractActionController::class));

        $actionMethodName = lcfirst($serviceName) . 'Action';
        self::assertTrue(
            $controllerClass->hasMethod($actionMethodName),
            'Expected ' . $actionMethodName . "; class:\n" . file_get_contents($fileName)
        );

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $expected   = [
            'controllers' => [
                'factories' => [
                    $controllerService => $className . 'Factory',
                ],
            ],
        ];
        self::assertEquals($expected, $config);
    }

    /**
     * @group feature/psr4
     */
    public function testCreateControllerRpcPSR4(): void
    {
        $this->module         = 'BazConf';
        $this->moduleEntity   = new ModuleEntity($this->module);
        $moduleUtils          = new ModuleUtils($this->moduleManager);
        $this->modulePathSpec = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRpc        = new RpcServiceModel(
            $this->moduleEntity,
            $this->modulePathSpec,
            $this->resource->factory($this->module)
        );

        $serviceName   = 'Bar';
        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src', __DIR__, $this->module);
        if (! is_dir($moduleSrcPath)) {
            mkdir($moduleSrcPath, 0775, true);
        }

        $result = $this->codeRpc->createController($serviceName);
        self::assertInstanceOf('stdClass', $result);
        self::assertObjectHasProperty('class', $result);
        self::assertObjectHasProperty('file', $result);
        self::assertObjectHasProperty('service', $result);

        $className         = sprintf("%s\\V1\\Rpc\\%s\\%sController", $this->module, $serviceName, $serviceName);
        $fileName          = sprintf(
            "%s/TestAsset/module/%s/src/V1/Rpc/%s/%sController.php",
            __DIR__,
            $this->module,
            $serviceName,
            $serviceName
        );
        $controllerService = sprintf("%s\\V1\\Rpc\\%s\\Controller", $this->module, $serviceName);

        self::assertEquals($className, $result->class);
        self::assertEquals(realpath($fileName), realpath($result->file));
        self::assertEquals($controllerService, $result->service);

        if (! class_exists($className)) {
            require_once $fileName;
        }
        $controllerClass = new ReflectionClass($className);
        self::assertTrue($controllerClass->isSubclassOf(AbstractActionController::class));

        $actionMethodName = lcfirst($serviceName) . 'Action';
        self::assertTrue(
            $controllerClass->hasMethod($actionMethodName),
            'Expected ' . $actionMethodName . "; class:\n" . file_get_contents($fileName)
        );

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $expected   = [
            'controllers' => [
                'factories' => [
                    $controllerService => $className . 'Factory',
                ],
            ],
        ];
        self::assertEquals($expected, $config);
    }

    public function testCanCreateRouteConfiguration(): object
    {
        $result = $this->codeRpc->createRoute(
            '/foo_conf/hello_world',
            'HelloWorld',
            'FooConf\Rpc\HelloWorld\Controller'
        );
        self::assertEquals('foo-conf.rpc.hello-world', $result);

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $expected   = [
            'router'               => [
                'routes' => [
                    'foo-conf.rpc.hello-world' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/foo_conf/hello_world',
                            'defaults' => [
                                'controller' => 'FooConf\Rpc\HelloWorld\Controller',
                                'action'     => 'helloWorld',
                            ],
                        ],
                    ],
                ],
            ],
            'api-tools-versioning' => [
                'uri' => [
                    'foo-conf.rpc.hello-world',
                ],
            ],
        ];
        self::assertEquals($expected, $config);
        return (object) [
            'config'             => $config,
            'config_file'        => $configFile,
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
        ];
    }

    public function testCanCreateRpcConfiguration(): object
    {
        $result   = $this->codeRpc->createRpcConfig(
            'HelloWorld',
            'FooConf\Rpc\HelloWorld\Controller',
            'foo-conf.rpc.hello-world',
            ['GET', 'PATCH']
        );
        $expected = [
            'api-tools-rpc' => [
                'FooConf\Rpc\HelloWorld\Controller' => [
                    'service_name' => 'HelloWorld',
                    'http_methods' => ['GET', 'PATCH'],
                    'route_name'   => 'foo-conf.rpc.hello-world',
                ],
            ],
        ];
        self::assertEquals($expected, $result);

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        self::assertEquals($expected, $config);

        return (object) [
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
            'config'             => $config,
            'config_file'        => $configFile,
        ];
    }

    /** @psalm-return array<string, array{0: ?string, 1: string}> */
    public function contentNegotiationSelectors(): array
    {
        return [
            'defaults' => [null, 'Json'],
            'HalJson'  => ['HalJson', 'HalJson'],
        ];
    }

    /**
     * @dataProvider contentNegotiationSelectors
     */
    public function testCanCreateContentNegotiationSelectorConfiguration(
        ?string $selector,
        string $expected
    ): object {
        $result   = $this->codeRpc->createContentNegotiationConfig('FooConf\Rpc\HelloWorld\Controller', $selector);
        $expected = [
            'api-tools-content-negotiation' => [
                'controllers'            => [
                    'FooConf\Rpc\HelloWorld\Controller' => $expected,
                ],
                'accept_whitelist'       => [
                    'FooConf\Rpc\HelloWorld\Controller' => [
                        'application/vnd.foo-conf.v1+json',
                        'application/json',
                        'application/*+json',
                    ],
                ],
                'content_type_whitelist' => [
                    'FooConf\Rpc\HelloWorld\Controller' => [
                        'application/vnd.foo-conf.v1+json',
                        'application/json',
                    ],
                ],
            ],
        ];
        self::assertEquals($expected, $result);

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        self::assertEquals($expected, $config);

        return (object) [
            'config'             => $config,
            'config_file'        => $configFile,
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
        ];
    }

    public function testCanGenerateAllArtifactsAtOnceViaCreateService(): object
    {
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $expected   = [
            'controllers'                   => [
                'factories' => [
                    'FooConf\V1\Rpc\HelloWorld\Controller' => 'FooConf\V1\Rpc\HelloWorld\HelloWorldControllerFactory',
                ],
            ],
            'router'                        => [
                'routes' => [
                    'foo-conf.rpc.hello-world' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/foo_conf/hello/world',
                            'defaults' => [
                                'controller' => 'FooConf\V1\Rpc\HelloWorld\Controller',
                                'action'     => 'helloWorld',
                            ],
                        ],
                    ],
                ],
            ],
            'api-tools-rpc'                 => [
                'FooConf\V1\Rpc\HelloWorld\Controller' => [
                    'service_name' => 'HelloWorld',
                    'http_methods' => ['GET', 'PATCH'],
                    'route_name'   => 'foo-conf.rpc.hello-world',
                ],
            ],
            'api-tools-content-negotiation' => [
                'controllers'            => [
                    'FooConf\V1\Rpc\HelloWorld\Controller' => $selector,
                ],
                'accept_whitelist'       => [
                    'FooConf\V1\Rpc\HelloWorld\Controller' => [
                        'application/vnd.foo-conf.v1+json',
                        'application/json',
                        'application/*+json',
                    ],
                ],
                'content_type_whitelist' => [
                    'FooConf\V1\Rpc\HelloWorld\Controller' => [
                        'application/vnd.foo-conf.v1+json',
                        'application/json',
                    ],
                ],
            ],
            'api-tools-versioning'          => [
                'uri' => [
                    'foo-conf.rpc.hello-world',
                ],
            ],
        ];
        $config     = include $configFile;
        self::assertEquals($expected, $config);

        $class     = 'FooConf\V1\Rpc\HelloWorld\HelloWorldController';
        $classFile = sprintf(
            '%s/TestAsset/module/FooConf/src/FooConf/V1/Rpc/HelloWorld/HelloWorldController.php',
            __DIR__
        );
        self::assertTrue(file_exists($classFile));

        $classFactoryFile = sprintf(
            '%s/TestAsset/module/FooConf/src/FooConf/V1/Rpc/HelloWorld/HelloWorldControllerFactory.php',
            __DIR__
        );
        self::assertTrue(file_exists($classFactoryFile));

        require_once $classFile;
        $controllerClass = new ReflectionClass($class);
        self::assertTrue($controllerClass->isSubclassOf(AbstractActionController::class));

        $actionMethodName = lcfirst($serviceName) . 'Action';
        self::assertTrue(
            $controllerClass->hasMethod($actionMethodName),
            'Expected ' . $actionMethodName . "; class:\n" . file_get_contents($classFile)
        );

        return (object) [
            'rpc_service' => $result->getArrayCopy(),
            'config_file' => $configFile,
            'config'      => $config,
        ];
    }

    /**
     * @depends testCanGenerateAllArtifactsAtOnceViaCreateService
     */
    public function testCanUpdateRoute(): void
    {
        // State is lost in between tests; re-seed the service
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $service     = $result->getArrayCopy();

        // and now do the actual work for the test
        self::assertTrue($this->codeRpc->updateRoute($service['controller_service_name'], '/api/hello/world'));
        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        self::assertEquals(
            '/api/hello/world',
            $config['router']['routes'][$service['route_name']]['options']['route']
        );
    }

    /**
     * @depends testCanCreateRpcConfiguration
     */
    public function testCanUpdateHttpMethods(object $configData): void
    {
        $methods = ['GET', 'PUT', 'DELETE'];
        $this->writer->toFile($configData->config_file, $configData->config);
        self::assertTrue($this->codeRpc->updateHttpMethods($configData->controller_service, $methods));
        $config = include $configData->config_file;
        self::assertEquals($methods, $config['api-tools-rpc'][$configData->controller_service]['http_methods']);
    }

    public function testCanUpdateContentNegotiationSelector(): void
    {
        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $this->writer->toFile($configFile, [
            'api-tools-content-negotiation' => [
                'controllers' => [
                    'FooConf\Rpc\HelloWorld\Controller' => 'Json',
                ],
            ],
        ]);
        self::assertTrue($this->codeRpc->updateSelector('FooConf\Rpc\HelloWorld\Controller', 'MyCustomSelector'));
        $config = include $configFile;
        self::assertEquals(
            'MyCustomSelector',
            $config['api-tools-content-negotiation']['controllers']['FooConf\Rpc\HelloWorld\Controller']
        );
    }

    public function testCanUpdateContentNegotiationWhitelists(): void
    {
        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $this->writer->toFile($configFile, [
            'api-tools-content-negotiation' => [
                'accept_whitelist'       => [
                    'FooConf\Rpc\HelloWorld\Controller' => [
                        'application/json',
                        'application/*+json',
                    ],
                ],
                'content_type_whitelist' => [
                    'FooConf\Rpc\HelloWorld\Controller' => [
                        'application/json',
                    ],
                ],
            ],
        ]);
        self::assertTrue(
            $this->codeRpc->updateContentNegotiationWhitelist(
                'FooConf\Rpc\HelloWorld\Controller',
                'accept',
                ['application/xml', 'application/*+xml']
            )
        );
        self::assertTrue(
            $this->codeRpc->updateContentNegotiationWhitelist(
                'FooConf\Rpc\HelloWorld\Controller',
                'content_type',
                ['application/xml']
            )
        );
        $config = include $configFile;
        self::assertEquals([
            'application/xml',
            'application/*+xml',
        ], $config['api-tools-content-negotiation']['accept_whitelist']['FooConf\Rpc\HelloWorld\Controller']);
        self::assertEquals([
            'application/xml',
        ], $config['api-tools-content-negotiation']['content_type_whitelist']['FooConf\Rpc\HelloWorld\Controller']);
    }

    public function testDeleteServiceRemovesExpectedConfigurationElements(): void
    {
        // State is lost in between tests; re-seed the service
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src/%s', __DIR__, $this->module, $this->module);
        $servicePath   = $moduleSrcPath . '/V1/Rpc/' . $serviceName;

        $this->codeRpc->deleteService($result);
        self::assertTrue(file_exists($servicePath));

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;

        self::assertIsArray($config);
        self::assertIsArray($config['api-tools-rpc']);
        self::assertIsArray($config['api-tools-versioning']);
        self::assertIsArray($config['router']['routes']);
        self::assertIsArray($config['api-tools-content-negotiation']);
        self::assertIsArray($config['controllers']);

        self::assertArrayNotHasKey($result->routeName, $config['router']['routes']);
        self::assertArrayNotHasKey($result->controllerServiceName, $config['api-tools-rpc']);
        self::assertArrayNotHasKey(
            $result->controllerServiceName,
            $config['api-tools-content-negotiation']['controllers']
        );
        self::assertArrayNotHasKey(
            $result->controllerServiceName,
            $config['api-tools-content-negotiation']['accept_whitelist']
        );
        self::assertArrayNotHasKey(
            $result->controllerServiceName,
            $config['api-tools-content-negotiation']['content_type_whitelist']
        );
        self::assertNotContains($result->routeName, $config['api-tools-versioning']['uri']);
        foreach ($config['controllers'] as $services) {
            self::assertArrayNotHasKey($result->controllerServiceName, $services);
        }
    }

    public function testDeleteServiceRecursive(): void
    {
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src/%s', __DIR__, $this->module, $this->module);
        $servicePath   = $moduleSrcPath . '/V1/Rpc/' . $serviceName;

        $this->codeRpc->deleteService($result, true);
        self::assertFalse(file_exists($servicePath));
    }

    /**
     * @group feature/psr4
     */
    public function testDeleteServiceRecursivePSR4(): void
    {
        $this->module         = 'BazConf';
        $moduleUtils          = new ModuleUtils($this->moduleManager);
        $this->moduleEntity   = new ModuleEntity($this->module);
        $this->modulePathSpec = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRpc        = new RpcServiceModel(
            $this->moduleEntity,
            $this->modulePathSpec,
            $this->resource->factory($this->module)
        );

        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src', __DIR__, $this->module);
        $servicePath   = $moduleSrcPath . '/V1/Rpc/' . $serviceName;
        $filepath      = $servicePath . "/" . $serviceName . "Controller.php";

        /** deleteService calls class_exists.  ensure that it's loaded in case the autoloader doesn't pick it up */
        if (file_exists($filepath)) {
            require_once $filepath;
        }

        $this->codeRpc->deleteService($result, true);
        self::assertFalse(file_exists($servicePath));
    }

    /**
     * @depends testDeleteServiceRemovesExpectedConfigurationElements
     */
    public function testDeletingNewerVersionOfServiceDoesNotRemoveRouteOrVersioningConfiguration(): void
    {
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        $path            = __DIR__ . '/TestAsset/module/FooConf';
        $versioningModel = new VersioningModel($this->resource->factory('FooConf'));
        self::assertTrue($versioningModel->createVersion('FooConf', 2));

        $serviceName = str_replace('1', '2', $result->controllerServiceName);
        $service     = $this->codeRpc->fetch($serviceName);
        self::assertTrue($this->codeRpc->deleteService($service));

        $config = include $path . '/config/module.config.php';
        self::assertIsArray($config);
        self::assertIsArray($config['api-tools-versioning']);
        self::assertIsArray($config['router']['routes']);

        self::assertArrayHasKey($result->controllerServiceName, $config['api-tools-rpc']);
        self::assertArrayNotHasKey($serviceName, $config['api-tools-rpc']);
        self::assertArrayHasKey($result->routeName, $config['router']['routes'], 'Route DELETED');
        self::assertContains($result->routeName, $config['api-tools-versioning']['uri'], 'Versioning DELETED');
    }

    /**
     * @group 72
     * @depends testCanCreateRpcConfiguration
     */
    public function testCanRemoveAllHttpVerbsWhenUpdating(object $configData): void
    {
        $methods = [];
        $this->writer->toFile($configData->config_file, $configData->config);
        self::assertTrue($this->codeRpc->updateHttpMethods($configData->controller_service, $methods));
        $config = include $configData->config_file;
        self::assertEquals($methods, $config['api-tools-rpc'][$configData->controller_service]['http_methods']);
    }

    public function testServiceExistsThrowExceptionAndLeaveConfigAsIs(): void
    {
        $serviceName = 'Foo';
        $route       = '/foo';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        $route = '/foo2';

        $this->expectException(RuntimeException::class);
        $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin/issues/49
     */
    public function testCreateServiceWithUrlAlreadyExist(): void
    {
        $serviceName = 'Foo';
        $route       = '/foo';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        // Create a new RPC entity with same URL match
        $serviceName = 'Bar';

        $this->expectException(RuntimeException::class);
        $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin/issues/49
     */
    public function testUpdateServiceWithUrlAlreadyExist(): void
    {
        $serviceName = 'Foo';
        $route       = '/foo';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        $serviceName = 'Bar';
        $route       = '/bar';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        self::assertInstanceOf(RpcServiceEntity::class, $result);

        $service = $result->getArrayCopy();

        // and now do the actual work for the test
        $this->expectException(RuntimeException::class);
        $this->codeRpc->updateRoute($service['controller_service_name'], '/foo');
    }
}
