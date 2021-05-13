<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Controller;

use InputFilter\Module;
use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Controller\InputFilterController;
use Laminas\ApiTools\Admin\Model\InputFilterCollection;
use Laminas\ApiTools\Admin\Model\InputFilterEntity;
use Laminas\ApiTools\Admin\Model\InputFilterModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParams;
use Laminas\ApiTools\ContentNegotiation\ParameterDataContainer;
use Laminas\ApiTools\Hal\Collection;
use Laminas\ApiTools\Hal\Entity;
use Laminas\Config\Writer\PhpArray;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;

use function copy;
use function json_encode;
use function unlink;

class InputFilterControllerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        require_once __DIR__ . '/../Model/TestAsset/module/InputFilter/Module.php';
        $modules = [
            'InputFilter' => new Module(),
        ];

        $this->moduleManager = $this->getMockBuilder(ModuleManager::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer        = new PhpArray();
        $moduleUtils         = new ModuleUtils($this->moduleManager);
        $this->configFactory = new ConfigResourceFactory($moduleUtils, $this->writer);
        $this->model         = new InputFilterModel($this->configFactory);
        $this->controller    = new InputFilterController($this->model);

        $this->basePath = __DIR__ . '/../Model/TestAsset/module/InputFilter/config';
        $this->config   = include $this->basePath . '/module.config.php';

        copy($this->basePath . '/module.config.php', $this->basePath . '/module.config.php.old');
    }

    public function tearDown()
    {
        copy($this->basePath . '/module.config.php.old', $this->basePath . '/module.config.php');
        unlink($this->basePath . '/module.config.php.old');
    }

    public function testGetInputFilters()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $params     = [
            'name'                    => $module,
            'controller_service_name' => $controller,
        ];
        $routeMatch = $this->createRouteMatch($params);
        $routeMatch->setMatchedRouteName('api-tools-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $result);
        $payload = $result->payload;
        $this->assertInstanceOf(Collection::class, $payload);
        $collection = $payload->getCollection();
        $this->assertInstanceOf(InputFilterCollection::class, $collection);
        $inputFilter = $collection->dequeue();
        $this->assertInstanceOf(InputFilterEntity::class, $inputFilter);

        $inputFilterKey                = $this->config['api-tools-content-validation'][$controller]['input_filter'];
        $expected                      = $this->config['input_filter_specs'][$inputFilterKey];
        $expected['input_filter_name'] = $inputFilterKey;
        $this->assertEquals($expected, $inputFilter->getArrayCopy());
    }

    public function testGetInputFilter()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $validator  = 'InputFilter\V1\Rest\Foo\Validator';
        $params     = [
            'name'                    => $module,
            'controller_service_name' => $controller,
            'input_filter_name'       => $validator,
        ];
        $routeMatch = $this->createRouteMatch($params);
        $routeMatch->setMatchedRouteName('api-tools-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $result);
        $payload = $result->payload;
        $this->assertInstanceOf(Entity::class, $payload);
        $entity = $payload->getEntity();
        $this->assertInstanceOf(InputFilterEntity::class, $entity);

        $expected                      = $this->config['input_filter_specs'][$validator];
        $expected['input_filter_name'] = $validator;
        $this->assertEquals($expected, $entity->getArrayCopy());
    }

    public function testAddInputFilter()
    {
        $inputFilter = [
            [
                'name'       => 'bar',
                'validators' => [
                    [
                        'name'    => 'NotEmpty',
                        'options' => [
                            'type' => 127,
                        ],
                    ],
                    [
                        'name' => 'Digits',
                    ],
                ],
            ],
        ];

        $request = new Request();
        $request->setMethod('put');
        $request->setContent(json_encode($inputFilter));
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $params     = [
            'name'                    => $module,
            'controller_service_name' => $controller,
        ];
        $routeMatch = $this->createRouteMatch($params);
        $routeMatch->setMatchedRouteName('api-tools-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams($inputFilter);
        $event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $plugins = new PluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $plugins->setInvokableClass('bodyParams', BodyParams::class);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);
        $this->controller->setPluginManager($plugins);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf(ViewModel::class, $result);
        $payload = $result->payload;
        $this->assertInstanceOf(Entity::class, $payload);
        $entity = $payload->getEntity();
        $this->assertInstanceOf(InputFilterEntity::class, $entity);

        $config                        = include $this->basePath . '/module.config.php';
        $validator                     = $config['api-tools-content-validation'][$controller]['input_filter'];
        $expected                      = $config['input_filter_specs'][$validator];
        $expected['input_filter_name'] = $validator;
        $this->assertEquals($expected, $entity->getArrayCopy());
    }

    public function testRemoveInputFilter()
    {
        $request = new Request();
        $request->setMethod('delete');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $validator  = 'InputFilter\V1\Rest\Foo\Validator';
        $params     = [
            'name'                    => $module,
            'controller_service_name' => $controller,
            'input_filter_name'       => $validator,
        ];
        $routeMatch = $this->createRouteMatch($params);
        $routeMatch->setMatchedRouteName('api-tools-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(204, $result->getStatusCode());
    }
}
