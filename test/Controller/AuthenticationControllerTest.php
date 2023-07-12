<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Controller\AuthenticationController;
use Laminas\ApiTools\Admin\Model\AuthenticationModel;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParam;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParams;
use Laminas\ApiTools\ContentNegotiation\ParameterDataContainer;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\Hal\Collection;
use Laminas\ApiTools\Hal\Entity;
use Laminas\Config\Writer\PhpArray as ConfigWriter;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Params;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\RouteMatch;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function array_diff_key;
use function copy;
use function count;
use function extension_loaded;
use function getenv;
use function unlink;

class AuthenticationControllerTest extends TestCase
{
    use ProphecyTrait;
    use RouteAssetsTrait;

    /** @var string */
    private $globalFile;
    /** @var string */
    private $localFile;
    /** @var AuthenticationController */
    private $controller;
    /** @var V2RouteMatch|RouteMatch */
    private $routeMatch;
    /** @var MvcEvent */
    private $event;

    public function setUp(): void
    {
        $this->globalFile = __DIR__ . '/TestAsset/Auth2/config/autoload/global.php';
        $this->localFile  = __DIR__ . '/TestAsset/Auth2/config/autoload/local.php';
        copy($this->globalFile . '.dist', $this->globalFile);
        copy($this->localFile . '.dist', $this->localFile);

        $writer = new ConfigWriter();
        $global = new ConfigResource(require $this->globalFile, $this->globalFile, $writer);
        $local  = new ConfigResource(require $this->localFile, $this->localFile, $writer);

        $moduleModel = $this->getMockBuilder(ModuleModel::class)
                            ->disableOriginalConstructor()
                            ->getMock();

        $model            = new AuthenticationModel($global, $local, $moduleModel);
        $this->controller = new AuthenticationController($model);

        $plugins = new ControllerPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $plugins->setService('bodyParams', new BodyParams());
        $plugins->setService('bodyParam', new BodyParam());
        $plugins->setService('params', new Params());
        $this->controller->setPluginManager($plugins);

        $this->routeMatch = $this->createRouteMatch();
        $this->routeMatch->setMatchedRouteName('api-tools/api/authentication');
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);

        $config = require __DIR__ . '/../../config/module.config.php';
        $router = $this->createRouter($config['router']);
        $this->event->setRouter($router);
        $this->controller->setEvent($this->event);
    }

    public function tearDown(): void
    {
        unlink($this->localFile);
        unlink($this->globalFile);
    }

    /** @psalm-return array<array-key, array{0: string}> */
    public function invalidRequestMethods(): array
    {
        return [
            ['patch'],
        ];
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemModel(string $method): void
    {
        $request = new Request();
        $request->setMethod($method);
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authenticationAction();
        self::assertInstanceOf(ApiProblemResponse::class, $result);
        $apiProblem = $result->getApiProblem();
        self::assertEquals(405, $apiProblem->status);
    }

    public function testGetAuthenticationRequest(): void
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $this->controller->setRequest($request);

        $params           = [
            'authentication_adapter' => 'testbasic',
        ];
        $this->routeMatch = $this->createRouteMatch($params);
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->authenticationAction();

        self::assertInstanceOf(ViewModel::class, $result);
        $payload = $result->getVariable('payload');
        self::assertInstanceOf(Entity::class, $payload);

        $metadata = $payload->getEntity();
        self::assertEquals('testbasic', $metadata['name']);
    }

    public function testGetAllAuthenticationRequest(): void
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authenticationAction();

        self::assertInstanceOf(ViewModel::class, $result);
        $payload = $result->getVariable('payload');
        self::assertInstanceOf(Collection::class, $payload);
        /** @psalm-var Entity[] */
        $collection = $payload->getCollection();
        foreach ($collection as $entity) {
            self::assertInstanceOf(Entity::class, $entity);
        }
        self::assertEquals(4, count($collection));
    }

    /**
     * Data for POST requests
     *
     * @psalm-return array<string, array{0: array<string, null|string>}>
     */
    public function postRequestData(): array
    {
        $data = [
            'htpasswd' => [
                [
                    'name'     => 'test',
                    'type'     => 'basic',
                    'realm'    => 'api',
                    'htpasswd' => __DIR__ . '/TestAsset/Auth2/config/autoload/htpasswd',
                ],
            ],
            'htdigest' => [
                [
                    'name'           => 'test2',
                    'type'           => 'digest',
                    'realm'          => 'api',
                    'nonce_timeout'  => '3600',
                    'digest_domains' => '/',
                    'htdigest'       => __DIR__ . '/TestAsset/Auth2/config/autoload/htdigest',
                ],
            ],
        ];
        if (extension_loaded('pdo_sqlite')) {
            $data['oauth2-sqlite'] = [
                [
                    'name'            => 'test3',
                    'type'            => 'oauth2',
                    'oauth2_type'     => 'pdo',
                    'oauth2_route'    => '/oauth-pdo',
                    'oauth2_dsn'      => 'sqlite:' . __DIR__ . '/TestAsset/Auth2/config/autoload/db.sqlite',
                    'oauth2_username' => null,
                    'oauth2_password' => null,
                    'oauth2_options'  => null,
                ],
            ];
        }
        if (extension_loaded('mongo')) {
            $data['oauth2-mongodb'] = [
                [
                    'name'                => 'test4',
                    'type'                => 'oauth2',
                    'oauth2_type'         => 'mongo',
                    'oauth2_route'        => '/oauth-mongo',
                    'oauth2_dsn'          => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_CONNECTSTRING'),
                    'oauth2_database'     => getenv('TESTS_LAMINAS_API_TOOLS_ADMIN_EXTMONGODB_DATABASE'),
                    'oauth2_locator_name' => null,
                    'oauth2_options'      => null,
                ],
            ];
        }
        return $data;
    }

    /**
     * @dataProvider postRequestData
     * @param array<string, mixed> $postData
     */
    public function testPostAuthenticationRequest(array $postData): void
    {
        $request = new Request();
        $request->setMethod('post');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $this->controller->setRequest($request);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams($postData);
        $this->event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $result = $this->controller->authenticationAction();
        self::assertInstanceOf(ViewModel::class, $result);
        $payload = $result->getVariable('payload');
        self::assertInstanceOf(Entity::class, $payload);
        $self = $payload->getLinks()->get('self');
        self::assertEquals('api-tools/api/authentication', $self->getRoute());
        $params = $self->getRouteParams();
        self::assertEquals($postData['name'], $params['authentication_adapter']);
    }

    /**
     * @dataProvider postRequestData
     * @param array<string, mixed> $postData
     */
    public function testPutAuthenticationRequest(array $postData): void
    {
        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $this->controller->setRequest($request);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams($postData);
        $this->event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $params           = [
            'authentication_adapter' => 'testbasic',
        ];
        $this->routeMatch = $this->createRouteMatch($params);
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->authenticationAction();
        self::assertInstanceOf(ViewModel::class, $result);
        $payload = $result->getVariable('payload');
        self::assertInstanceOf(Entity::class, $payload);
        $self = $payload->getLinks()->get('self');
        self::assertEquals('api-tools/api/authentication', $self->getRoute());
        $params = $self->getRouteParams();
        self::assertEquals('testbasic', $params['authentication_adapter']);

        $metadata = $payload->getEntity();
        self::assertEmpty(array_diff_key($metadata, $postData));
    }

    public function testDeleteAuthenticationRequest(): void
    {
        $request = new Request();
        $request->setMethod('delete');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $this->controller->setRequest($request);

        $params           = [
            'authentication_adapter' => 'testbasic',
        ];
        $this->routeMatch = $this->createRouteMatch($params);
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->authenticationAction();
        self::assertInstanceOf(Response::class, $result);
        self::assertEquals(204, $result->getStatusCode());
    }

    public function testGetAuthenticationMapRequest(): void
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getQuery()->set('version', 1);
        $this->controller->setRequest($request);

        $params           = [
            'name' => 'Status',
        ];
        $this->routeMatch = $this->createRouteMatch($params);
        $this->routeMatch->setMatchedRouteName('api-tools/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        self::assertInstanceOf(ViewModel::class, $result);
        $config = require $this->globalFile;
        self::assertEquals(
            $config['api-tools-mvc-auth']['authentication']['map']['Status\V1'],
            $result->getVariable('authentication')
        );
    }

    public function testGetWrongAuthenticationMapRequest(): void
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getQuery()->set('version', 1);
        $this->controller->setRequest($request);

        $params           = [
            'name' => 'Status2',
        ];
        $this->routeMatch = $this->createRouteMatch($params);
        $this->routeMatch->setMatchedRouteName('api-tools/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        self::assertInstanceOf(ViewModel::class, $result);
        self::assertFalse($result->getVariable('authentication'));
    }

    public function testAddAuthenticationMapRequest(): void
    {
        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $this->controller->setRequest($request);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams([
            'authentication' => 'testoauth2pdo',
        ]);
        $this->event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $params           = [
            'name' => 'Foo',
        ];
        $this->routeMatch = $this->createRouteMatch($params);
        $this->routeMatch->setMatchedRouteName('api-tools/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        self::assertInstanceOf(ViewModel::class, $result);
        self::assertEquals('testoauth2pdo', $result->getVariable('authentication'));
    }

    public function testUpdateAuthenticationMapRequest(): void
    {
        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getQuery()->set('version', 2);
        $this->controller->setRequest($request);

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParams([
            'authentication' => 'testoauth2mongo',
        ]);
        $this->event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $params           = [
            'name' => 'Status',
        ];
        $this->routeMatch = $this->createRouteMatch($params);
        $this->routeMatch->setMatchedRouteName('api-tools/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        self::assertInstanceOf(ViewModel::class, $result);
        self::assertEquals('testoauth2mongo', $result->getVariable('authentication'));
    }

    public function testRemoveAuthenticationMapRequest(): void
    {
        $request = new Request();
        $request->setMethod('delete');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getQuery()->set('version', 1);
        $this->controller->setRequest($request);

        $params           = [
            'name' => 'Status',
        ];
        $this->routeMatch = $this->createRouteMatch($params);
        $this->routeMatch->setMatchedRouteName('api-tools/api/module/authentication');
        $this->event->setRouteMatch($this->routeMatch);

        $result = $this->controller->mappingAction();
        self::assertInstanceOf(Response::class, $result);
        self::assertEquals(204, $result->getStatusCode());
    }
}
