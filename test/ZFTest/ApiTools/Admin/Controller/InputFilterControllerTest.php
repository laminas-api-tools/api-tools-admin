<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Controller\InputFilterController;
use Laminas\ApiTools\Admin\Model\InputFilterModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;
use Laminas\ApiTools\ContentNegotiation\ParameterDataContainer;
use Laminas\Config\Writer\PhpArray;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase as TestCase;

class InputFilterControllerTest extends TestCase
{
    public function setUp()
    {
        require_once __DIR__ . '/../Model/TestAsset/module/InputFilter/Module.php';
        $modules = array(
            'InputFilter' => new \InputFilter\Module()
        );

        $this->moduleManager = $this->getMockBuilder('Laminas\ModuleManager\ModuleManager')
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

        $this->basePath      = __DIR__ . '/../Model/TestAsset/module/InputFilter/config';
        $this->config        = include $this->basePath . '/module.config.php';

        copy($this->basePath . '/module.config.php', $this->basePath . '/module.config.php.old');
    }

    public function tearDown()
    {
        copy($this->basePath .'/module.config.php.old', $this->basePath . '/module.config.php');
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
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller
        );
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName('api-tools-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);
        $payload = $result->payload;
        $this->assertInstanceOf('Laminas\ApiTools\Hal\Collection', $payload);
        $collection = $payload->collection;
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\InputFilterCollection', $collection);
        $inputFilter = $collection->dequeue();
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\InputFilterEntity', $inputFilter);

        $inputFilterKey = $this->config['api-tools-content-validation'][$controller]['input_filter'];
        $expected = $this->config['input_filters'][$inputFilterKey];
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
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller,
            'input_filter_name' => $validator,
        );
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName('api-tools-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);
        $payload = $result->payload;
        $this->assertInstanceOf('Laminas\ApiTools\Hal\Resource', $payload);
        $resource = $payload->resource;
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\InputFilterEntity', $resource);

        $expected = $this->config['input_filters'][$validator];
        $expected['input_filter_name'] = $validator;
        $this->assertEquals($expected, $resource->getArrayCopy());
    }

    public function testAddInputFilter()
    {
        $inputfilter = [
            [
                'name' => 'bar',
                'validators' => [
                    [
                        'name' => 'NotEmpty',
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
        $request->setContent(json_encode($inputfilter));
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $module     = 'InputFilter';
        $controller = 'InputFilter\V1\Rest\Foo\Controller';
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller
        );
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName('api-tools-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams($inputfilter);
        $event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $plugins = new PluginManager();
        $plugins->setInvokableClass('bodyParams', 'Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParams');

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);
        $this->controller->setPluginManager($plugins);

        $result     = $this->controller->indexAction();
        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);
        $payload = $result->payload;
        $this->assertInstanceOf('Laminas\ApiTools\Hal\Resource', $payload);
        $resource = $payload->resource;
        $this->assertInstanceOf('Laminas\ApiTools\Admin\Model\InputFilterEntity', $resource);

        $config    = include $this->basePath . '/module.config.php';
        $validator = $config['api-tools-content-validation'][$controller]['input_filter'];
        $expected  = $config['input_filters'][$validator];
        $expected['input_filter_name'] = $validator;
        $this->assertEquals($expected, $resource->getArrayCopy());
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
        $params = array(
            'name' => $module,
            'controller_service_name' => $controller,
            'input_filter_name' => $validator,
        );
        $routeMatch = new RouteMatch($params);
        $routeMatch->setMatchedRouteName('api-tools-admin/api/module/rest-service/rest_input_filter');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $result = $this->controller->indexAction();
        $this->assertInstanceOf('Laminas\Http\Response', $result);
        $this->assertEquals(204, $result->getStatusCode());
    }
}
