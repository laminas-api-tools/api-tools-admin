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
use Prophecy\PhpUnit\ProphecyTrait;

use function copy;
use function json_encode;
use function unlink;

class InputFilterControllerTest extends TestCase
{
    use ProphecyTrait;
    use RouteAssetsTrait;

    /** @var InputFilterController */
    private $controller;
    /** @var string */
    private $basePath;
    /** @var mixed */
    private $config;

    public function setUp(): void
    {
        require_once __DIR__ . '/../Model/TestAsset/module/InputFilter/Module.php';
        $modules = [
            'InputFilter' => new Module(),
        ];

        $moduleManager = $this->getMockBuilder(ModuleManager::class)
                              ->disableOriginalConstructor()
                              ->getMock();
        $moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $writer           = new PhpArray();
        $moduleUtils      = new ModuleUtils($moduleManager);
        $configFactory    = new ConfigResourceFactory($moduleUtils, $writer);
        $model            = new InputFilterModel($configFactory);
        $this->controller = new InputFilterController($model);

        $this->basePath = __DIR__ . '/../Model/TestAsset/module/InputFilter/config';
        $this->config   = include $this->basePath . '/module.config.php';

        copy($this->basePath . '/module.config.php', $this->basePath . '/module.config.php.old');
    }

    public function tearDown(): void
    {
        copy($this->basePath . '/module.config.php.old', $this->basePath . '/module.config.php');
        unlink($this->basePath . '/module.config.php.old');
    }

    public function testGetInputFilters(): void
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
        self::assertInstanceOf(ViewModel::class, $result);
        $payload = $result->payload;
        self::assertInstanceOf(Collection::class, $payload);
        $collection = $payload->getCollection();
        self::assertInstanceOf(InputFilterCollection::class, $collection);
        $inputFilter = $collection->dequeue();
        self::assertInstanceOf(InputFilterEntity::class, $inputFilter);

        $inputFilterKey                = $this->config['api-tools-content-validation'][$controller]['input_filter'];
        $expected                      = $this->config['input_filter_specs'][$inputFilterKey];
        $expected['input_filter_name'] = $inputFilterKey;
        self::assertEquals($expected, $inputFilter->getArrayCopy());
    }

    public function testGetInputFilter(): void
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
        self::assertInstanceOf(ViewModel::class, $result);
        $payload = $result->payload;
        self::assertInstanceOf(Entity::class, $payload);
        $entity = $payload->getEntity();
        self::assertInstanceOf(InputFilterEntity::class, $entity);

        $expected                      = $this->config['input_filter_specs'][$validator];
        $expected['input_filter_name'] = $validator;
        self::assertEquals($expected, $entity->getArrayCopy());
    }

    public function testAddInputFilter(): void
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
        self::assertInstanceOf(ViewModel::class, $result);
        $payload = $result->payload;
        self::assertInstanceOf(Entity::class, $payload);
        $entity = $payload->getEntity();
        self::assertInstanceOf(InputFilterEntity::class, $entity);

        $config                        = include $this->basePath . '/module.config.php';
        $validator                     = $config['api-tools-content-validation'][$controller]['input_filter'];
        $expected                      = $config['input_filter_specs'][$validator];
        $expected['input_filter_name'] = $validator;
        self::assertEquals($expected, $entity->getArrayCopy());
    }

    public function testRemoveInputFilter(): void
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
        self::assertInstanceOf(Response::class, $result);
        self::assertEquals(204, $result->getStatusCode());
    }
}
