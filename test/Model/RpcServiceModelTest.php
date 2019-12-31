<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Model;

use BazConf;
use FooConf;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModulePathSpec;
use Laminas\ApiTools\Admin\Model\RpcServiceModel;
use Laminas\ApiTools\Admin\Model\VersioningModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory;
use Laminas\Config\Writer\PhpArray;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RpcServiceModelTest extends TestCase
{
    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return bool
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

    public function setUp()
    {
        $this->module = 'FooConf';
        $this->cleanUpAssets();

        $modules = [
            'FooConf' => new FooConf\Module(),
            'BazConf' => new BazConf\Module(),
        ];

        $this->moduleEntity  = new ModuleEntity($this->module);
        $this->moduleManager = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer   = new PhpArray();
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->modulePathSpec  = new ModulePathSpec($moduleUtils);
        $this->resource = new ResourceFactory($moduleUtils, $this->writer);
        $this->codeRpc  = new RpcServiceModel(
            $this->moduleEntity,
            $this->modulePathSpec,
            $this->resource->factory($this->module)
        );
    }

    protected function setCurrentModule()
    {
    }

    public function tearDown()
    {
        $this->cleanUpAssets();
    }

    public function testRejectSpacesInRpcServiceName1()
    {
        /**
         * @todo define exception in Rpc namespace
         */
        $this->expectException('Laminas\ApiTools\Rest\Exception\CreationException');
        $this->codeRpc->createService('Foo Bar', 'route', []);
    }

    public function testRejectSpacesInRpcServiceName2()
    {
        /**
         * @todo define exception in Rpc namespace
        */
        $this->expectException('Laminas\ApiTools\Rest\Exception\CreationException');
        $this->codeRpc->createService('Foo:Bar', 'route', []);
    }

    public function testRejectSpacesInRpcServiceName3()
    {
        /**
         * @todo define exception in Rpc namespace
        */
        $this->expectException('Laminas\ApiTools\Rest\Exception\CreationException');
        $this->codeRpc->createService('Foo/Bar', 'route', []);
    }

    /**
     * @group createController
     */
    public function testCanCreateControllerServiceNameFromResourceNameSpace()
    {
        $this->markTestSkipped('Invalid use case');

        /**
         * @todo is this the expected behavior?
        */
        $this->assertEquals(
            'FooConf\V1\Rpc\Baz\Bat\Foo\Baz\Bat\FooController',
            $this->codeRpc->createController('Baz\Bat\Foo')->class
        );
    }

    public function testCreateControllerRpc()
    {
        $serviceName = 'Bar';
        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src/%s', __DIR__, $this->module, $this->module);
        if (! is_dir($moduleSrcPath)) {
            mkdir($moduleSrcPath, 0775, true);
        }

        $result = $this->codeRpc->createController($serviceName);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('class', $result);
        $this->assertObjectHasAttribute('file', $result);
        $this->assertObjectHasAttribute('service', $result);

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

        $this->assertEquals($className, $result->class);
        $this->assertEquals(realpath($fileName), realpath($result->file));
        $this->assertEquals($controllerService, $result->service);

        require_once $fileName;
        $controllerClass = new ReflectionClass($className);
        $this->assertTrue($controllerClass->isSubclassOf('Laminas\Mvc\Controller\AbstractActionController'));

        $actionMethodName = lcfirst($serviceName) . 'Action';
        $this->assertTrue(
            $controllerClass->hasMethod($actionMethodName),
            'Expected ' . $actionMethodName . "; class:\n" . file_get_contents($fileName)
        );

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $expected = [
            'controllers' => ['factories' => [
                $controllerService => $className . 'Factory',
            ]],
        ];
        $this->assertEquals($expected, $config);
    }

    /**
     * @group feature/psr4
     */
    public function testCreateControllerRpcPSR4()
    {
        $this->module = 'BazConf';
        $this->moduleEntity  = new ModuleEntity($this->module);
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->modulePathSpec  = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRpc  = new RpcServiceModel(
            $this->moduleEntity,
            $this->modulePathSpec,
            $this->resource->factory($this->module)
        );

        $serviceName = 'Bar';
        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src', __DIR__, $this->module);
        if (! is_dir($moduleSrcPath)) {
            mkdir($moduleSrcPath, 0775, true);
        }

        $result = $this->codeRpc->createController($serviceName);
        $this->assertInstanceOf('stdClass', $result);
        $this->assertObjectHasAttribute('class', $result);
        $this->assertObjectHasAttribute('file', $result);
        $this->assertObjectHasAttribute('service', $result);

        $className         = sprintf("%s\\V1\\Rpc\\%s\\%sController", $this->module, $serviceName, $serviceName);
        $fileName          = sprintf(
            "%s/TestAsset/module/%s/src/V1/Rpc/%s/%sController.php",
            __DIR__,
            $this->module,
            $serviceName,
            $serviceName
        );
        $controllerService = sprintf("%s\\V1\\Rpc\\%s\\Controller", $this->module, $serviceName);

        $this->assertEquals($className, $result->class);
        $this->assertEquals(realpath($fileName), realpath($result->file));
        $this->assertEquals($controllerService, $result->service);

        require_once $fileName;
        $controllerClass = new ReflectionClass($className);
        $this->assertTrue($controllerClass->isSubclassOf('Laminas\Mvc\Controller\AbstractActionController'));

        $actionMethodName = lcfirst($serviceName) . 'Action';
        $this->assertTrue(
            $controllerClass->hasMethod($actionMethodName),
            'Expected ' . $actionMethodName . "; class:\n" . file_get_contents($fileName)
        );

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $expected = [
            'controllers' => ['factories' => [
                $controllerService => $className . 'Factory',
            ]],
        ];
        $this->assertEquals($expected, $config);
    }

    public function testCanCreateRouteConfiguration()
    {
        $result = $this->codeRpc->createRoute(
            '/foo_conf/hello_world',
            'HelloWorld',
            'FooConf\Rpc\HelloWorld\Controller'
        );
        $this->assertEquals('foo-conf.rpc.hello-world', $result);

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $expected   = [
            'router' => ['routes' => [
                'foo-conf.rpc.hello-world' => [
                    'type' => 'Segment',
                    'options' => [
                        'route' => '/foo_conf/hello_world',
                        'defaults' => [
                            'controller' => 'FooConf\Rpc\HelloWorld\Controller',
                            'action' => 'helloWorld',
                        ],
                    ],
                ],
            ]],
            'api-tools-versioning' => [
                'uri' => [
                    'foo-conf.rpc.hello-world',
                ],
            ],
        ];
        $this->assertEquals($expected, $config);
        return (object) [
            'config'             => $config,
            'config_file'        => $configFile,
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
        ];
    }

    public function testCanCreateRpcConfiguration()
    {
        $result = $this->codeRpc->createRpcConfig(
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
        $this->assertEquals($expected, $result);

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $this->assertEquals($expected, $config);

        return (object) [
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
            'config'             => $config,
            'config_file'        => $configFile,
        ];
    }

    public function contentNegotiationSelectors()
    {
        return [
            'defaults' => [null, 'Json'],
            'HalJson' => ['HalJson', 'HalJson'],
        ];
    }

    /**
     * @dataProvider contentNegotiationSelectors
     */
    public function testCanCreateContentNegotiationSelectorConfiguration($selector, $expected)
    {
        $result = $this->codeRpc->createContentNegotiationConfig('FooConf\Rpc\HelloWorld\Controller', $selector);
        $expected = [
            'api-tools-content-negotiation' => [
                'controllers' => [
                    'FooConf\Rpc\HelloWorld\Controller' => $expected,
                ],
                'accept_whitelist' => [
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
        $this->assertEquals($expected, $result);

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $this->assertEquals($expected, $config);

        return (object) [
            'config'             => $config,
            'config_file'        => $configFile,
            'controller_service' => 'FooConf\Rpc\HelloWorld\Controller',
        ];
    }

    public function testCanGenerateAllArtifactsAtOnceViaCreateService()
    {
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $expected   = [
            'controllers' => ['factories' => [
                'FooConf\V1\Rpc\HelloWorld\Controller' => 'FooConf\V1\Rpc\HelloWorld\HelloWorldControllerFactory',
            ]],
            'router' => ['routes' => [
                'foo-conf.rpc.hello-world' => [
                    'type' => 'Segment',
                    'options' => [
                        'route' => '/foo_conf/hello/world',
                        'defaults' => [
                            'controller' => 'FooConf\V1\Rpc\HelloWorld\Controller',
                            'action' => 'helloWorld',
                        ],
                    ],
                ],
            ]],
            'api-tools-rpc' => [
                'FooConf\V1\Rpc\HelloWorld\Controller' => [
                    'service_name' => 'HelloWorld',
                    'http_methods' => ['GET', 'PATCH'],
                    'route_name'   => 'foo-conf.rpc.hello-world',
                ],
            ],
            'api-tools-content-negotiation' => [
                'controllers' => [
                    'FooConf\V1\Rpc\HelloWorld\Controller' => $selector,
                ],
                'accept_whitelist' => [
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
            'api-tools-versioning' => [
                'uri' => [
                    'foo-conf.rpc.hello-world',
                ],
            ],
        ];
        $config = include $configFile;
        $this->assertEquals($expected, $config);

        $class     = 'FooConf\V1\Rpc\HelloWorld\HelloWorldController';
        $classFile = sprintf(
            '%s/TestAsset/module/FooConf/src/FooConf/V1/Rpc/HelloWorld/HelloWorldController.php',
            __DIR__
        );
        $this->assertTrue(file_exists($classFile));

        $classFactoryFile = sprintf(
            '%s/TestAsset/module/FooConf/src/FooConf/V1/Rpc/HelloWorld/HelloWorldControllerFactory.php',
            __DIR__
        );
        $this->assertTrue(file_exists($classFactoryFile));

        require_once $classFile;
        $controllerClass = new ReflectionClass($class);
        $this->assertTrue($controllerClass->isSubclassOf('Laminas\Mvc\Controller\AbstractActionController'));

        $actionMethodName = lcfirst($serviceName) . 'Action';
        $this->assertTrue(
            $controllerClass->hasMethod($actionMethodName),
            'Expected ' . $actionMethodName . "; class:\n" . file_get_contents($classFile)
        );

        return (object) [
            'rpc_service' => $result->getArrayCopy(),
            'config_file'  => $configFile,
            'config'       => $config,
        ];
    }

    /**
     * @depends testCanGenerateAllArtifactsAtOnceViaCreateService
     */
    public function testCanUpdateRoute($data)
    {
        // State is lost in between tests; re-seed the service
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $service    = $result->getArrayCopy();

        // and now do the actual work for the test
        $this->assertTrue($this->codeRpc->updateRoute($service['controller_service_name'], '/api/hello/world'));
        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;
        $this->assertEquals(
            '/api/hello/world',
            $config['router']['routes'][$service['route_name']]['options']['route']
        );
    }

    /**
     * @depends testCanCreateRpcConfiguration
     */
    public function testCanUpdateHttpMethods($configData)
    {
        $methods = ['GET', 'PUT', 'DELETE'];
        $this->writer->toFile($configData->config_file, $configData->config);
        $this->assertTrue($this->codeRpc->updateHttpMethods($configData->controller_service, $methods));
        $config = include $configData->config_file;
        $this->assertEquals($methods, $config['api-tools-rpc'][$configData->controller_service]['http_methods']);
    }

    public function testCanUpdateContentNegotiationSelector()
    {
        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $this->writer->toFile($configFile, [
            'api-tools-content-negotiation' => [
                'controllers' => [
                    'FooConf\Rpc\HelloWorld\Controller' => 'Json',
                ],
            ],
        ]);
        $this->assertTrue($this->codeRpc->updateSelector('FooConf\Rpc\HelloWorld\Controller', 'MyCustomSelector'));
        $config = include $configFile;
        $this->assertEquals(
            'MyCustomSelector',
            $config['api-tools-content-negotiation']['controllers']['FooConf\Rpc\HelloWorld\Controller']
        );
    }

    public function testCanUpdateContentNegotiationWhitelists()
    {
        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $this->writer->toFile($configFile, [
            'api-tools-content-negotiation' => [
                'accept_whitelist' => [
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
        $this->assertTrue(
            $this->codeRpc->updateContentNegotiationWhitelist(
                'FooConf\Rpc\HelloWorld\Controller',
                'accept',
                ['application/xml', 'application/*+xml']
            )
        );
        $this->assertTrue(
            $this->codeRpc->updateContentNegotiationWhitelist(
                'FooConf\Rpc\HelloWorld\Controller',
                'content_type',
                ['application/xml']
            )
        );
        $config = include $configFile;
        $this->assertEquals([
            'application/xml',
            'application/*+xml',
        ], $config['api-tools-content-negotiation']['accept_whitelist']['FooConf\Rpc\HelloWorld\Controller']);
        $this->assertEquals([
            'application/xml',
        ], $config['api-tools-content-negotiation']['content_type_whitelist']['FooConf\Rpc\HelloWorld\Controller']);
    }

    public function testDeleteServiceRemovesExpectedConfigurationElements()
    {
        // State is lost in between tests; re-seed the service
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src/%s', __DIR__, $this->module, $this->module);
        $servicePath = $moduleSrcPath . '/V1/Rpc/' . $serviceName;

        $this->codeRpc->deleteService($result);
        $this->assertTrue(file_exists($servicePath));

        $configFile = $this->modulePathSpec->getModuleConfigFilePath($this->module);
        $config     = include $configFile;

        $this->assertInternalType('array', $config);
        $this->assertInternalType('array', $config['api-tools-rpc']);
        $this->assertInternalType('array', $config['api-tools-versioning']);
        $this->assertInternalType('array', $config['router']['routes']);
        $this->assertInternalType('array', $config['api-tools-content-negotiation']);
        $this->assertInternalType('array', $config['controllers']);

        $this->assertArrayNotHasKey($result->routeName, $config['router']['routes']);
        $this->assertArrayNotHasKey($result->controllerServiceName, $config['api-tools-rpc']);
        $this->assertArrayNotHasKey(
            $result->controllerServiceName,
            $config['api-tools-content-negotiation']['controllers']
        );
        $this->assertArrayNotHasKey(
            $result->controllerServiceName,
            $config['api-tools-content-negotiation']['accept_whitelist']
        );
        $this->assertArrayNotHasKey(
            $result->controllerServiceName,
            $config['api-tools-content-negotiation']['content_type_whitelist']
        );
        $this->assertNotContains($result->routeName, $config['api-tools-versioning']['uri']);
        foreach ($config['controllers'] as $serviceType => $services) {
            $this->assertArrayNotHasKey($result->controllerServiceName, $services);
        }
    }

    public function testDeleteServiceRecursive()
    {
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src/%s', __DIR__, $this->module, $this->module);
        $servicePath = $moduleSrcPath . '/V1/Rpc/' . $serviceName;

        $this->codeRpc->deleteService($result, true);
        $this->assertFalse(file_exists($servicePath));
    }

    /**
     * @group feature/psr4
     */
    public function testDeleteServiceRecursivePSR4()
    {
        $this->module = 'BazConf';
        $moduleUtils    = new ModuleUtils($this->moduleManager);
        $this->moduleEntity  = new ModuleEntity($this->module);
        $this->modulePathSpec  = new ModulePathSpec($moduleUtils, 'psr-4', __DIR__ . '/TestAsset');
        $this->codeRpc  = new RpcServiceModel(
            $this->moduleEntity,
            $this->modulePathSpec,
            $this->resource->factory($this->module)
        );

        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src', __DIR__, $this->module);
        $servicePath = $moduleSrcPath . '/V1/Rpc/' . $serviceName;
        $filepath = $servicePath . "/". $serviceName . "Controller.php";

        /** deleteService calls class_exists.  ensure that it's loaded in case the autoloader doesn't pick it up */
        if (file_exists($filepath)) {
            require_once $filepath;
        }

        $this->codeRpc->deleteService($result, true);
        $this->assertFalse(file_exists($servicePath));
    }

    /**
     * @depends testDeleteServiceRemovesExpectedConfigurationElements
     */
    public function testDeletingNewerVersionOfServiceDoesNotRemoveRouteOrVersioningConfiguration()
    {
        $serviceName = 'HelloWorld';
        $route       = '/foo_conf/hello/world';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        $path = __DIR__ . '/TestAsset/module/FooConf';
        $versioningModel = new VersioningModel($this->resource->factory('FooConf'));
        $this->assertTrue($versioningModel->createVersion('FooConf', 2));

        $serviceName = str_replace('1', '2', $result->controllerServiceName);
        $service = $this->codeRpc->fetch($serviceName);
        $this->assertTrue($this->codeRpc->deleteService($service));

        $config = include $path . '/config/module.config.php';
        $this->assertInternalType('array', $config);
        $this->assertInternalType('array', $config['api-tools-versioning']);
        $this->assertInternalType('array', $config['router']['routes']);

        $this->assertArrayHasKey($result->controllerServiceName, $config['api-tools-rpc']);
        $this->assertArrayNotHasKey($serviceName, $config['api-tools-rpc']);
        $this->assertArrayHasKey($result->routeName, $config['router']['routes'], 'Route DELETED');
        $this->assertContains($result->routeName, $config['api-tools-versioning']['uri'], 'Versioning DELETED');
    }

    /**
     * @group 72
     * @depends testCanCreateRpcConfiguration
     */
    public function testCanRemoveAllHttpVerbsWhenUpdating($configData)
    {
        $methods = [];
        $this->writer->toFile($configData->config_file, $configData->config);
        $this->assertTrue($this->codeRpc->updateHttpMethods($configData->controller_service, $methods));
        $config = include $configData->config_file;
        $this->assertEquals($methods, $config['api-tools-rpc'][$configData->controller_service]['http_methods']);
    }

    /**
     * @expectedException Laminas\ApiTools\Admin\Exception\RuntimeException
     */
    public function testServiceExistsThrowExceptionAndLeaveConfigAsIs()
    {
        $serviceName = 'Foo';
        $route       = '/foo';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        $route       = '/foo2';
        $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin/issues/49
     * @expectedException Laminas\ApiTools\Admin\Exception\RuntimeException
     */
    public function testCreateServiceWithUrlAlreadyExist()
    {
        $serviceName = 'Foo';
        $route       = '/foo';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        // Create a new RPC entity with same URL match
        $serviceName = 'Bar';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
    }

    /**
     * @see https://github.com/zfcampus/zf-apigility-admin/issues/49
     * @expectedException Laminas\ApiTools\Admin\Exception\RuntimeException
     */
    public function testUpdateServiceWithUrlAlreadyExist()
    {
        $serviceName = 'Foo';
        $route       = '/foo';
        $httpMethods = ['GET', 'PATCH'];
        $selector    = 'HalJson';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        $serviceName = 'Bar';
        $route       = '/bar';
        $result      = $this->codeRpc->createService($serviceName, $route, $httpMethods, $selector);
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\RpcServiceEntity', $result);

        $service    = $result->getArrayCopy();
        // and now do the actual work for the test
        $this->codeRpc->updateRoute($service['controller_service_name'], '/foo');
    }
}
