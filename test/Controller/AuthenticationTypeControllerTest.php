<?php

namespace LaminasTest\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Controller\AuthenticationTypeController;
use Laminas\ApiTools\MvcAuth\Authentication\DefaultAuthenticationListener as AuthListener;
use Laminas\Http\Request;
use Laminas\Mvc\MvcEvent;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;

class AuthenticationTypeControllerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->globalFile = __DIR__ . '/TestAsset/Auth2/config/autoload/global.php';
        $this->localFile  = __DIR__ . '/TestAsset/Auth2/config/autoload/local.php';
        copy($this->globalFile . '.dist', $this->globalFile);
        copy($this->localFile . '.dist', $this->localFile);

        $this->controller = $this->getController($this->localFile, $this->globalFile);

        $this->routeMatch = $this->createRouteMatch([]);
        $this->routeMatch->setMatchedRouteName('api-tools/api/authentication-type');
        $this->event = new MvcEvent();
        $this->event->setRouteMatch($this->routeMatch);

        $this->controller->setEvent($this->event);
    }

    protected function getController($localFile, $globalFile)
    {
        $authListener = new AuthListener();
        $config = array_merge(require $globalFile, require $localFile);

        /* Register old authentication adapter types */
        if (isset($config['api-tools-oauth2'])) {
            $authListener->addAuthenticationTypes(['oauth2']);
        } elseif (isset($config['api-tools-mvc-auth']['authentication']['http'])) {
            $types = [];
            if (isset($config['api-tools-mvc-auth']['authentication']['http']['htpasswd'])) {
                $types[] = 'basic';
            }
            if (isset($config['api-tools-mvc-auth']['authentication']['http']['htdigest'])) {
                $types[] = 'digest';
            }
            $authListener->addAuthenticationTypes($types);
        }

        /* Register v1.1+ adapter types */
        if (isset($config['api-tools-mvc-auth']['authentication']['adapters'])) {
            foreach ($config['api-tools-mvc-auth']['authentication']['adapters'] as $adapter => $adapterConfig) {
                if (! isset($adapterConfig['adapter'])) {
                    continue;
                }
                if (false !== stristr($adapterConfig['adapter'], 'http')) {
                    if (isset($adapterConfig['options']['htpasswd'])) {
                        $authListener->addAuthenticationTypes([$adapter . '-' . 'basic']);
                    }
                    if (isset($adapterConfig['options']['htdigest'])) {
                        $authListener->addAuthenticationTypes([$adapter . '-' . 'digest']);
                    }
                    continue;
                }
                $authListener->addAuthenticationTypes([$adapter]);
            }
        }

        return new AuthenticationTypeController($authListener);
    }

    public function tearDown()
    {
        unlink($this->localFile);
        unlink($this->globalFile);
    }

    public function invalidRequestMethods()
    {
        return [
            ['post', 'put', 'patch', 'delete'],
        ];
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemModel($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authTypeAction();
        $this->assertInstanceOf('Laminas\ApiTools\ApiProblem\ApiProblemResponse', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }


    public function testGetAuthenticationRequest()
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $this->controller->setRequest($request);

        $result = $this->controller->authTypeAction();

        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);
        $config = require $this->localFile;
        $adapters = array_keys($config['api-tools-mvc-auth']['authentication']['adapters']);

        foreach ($config['api-tools-mvc-auth']['authentication']['adapters'] as $adapter => $adapterConfig) {
            if (false === stristr($adapterConfig['adapter'], 'http')) {
                continue;
            }
            if (isset($adapterConfig['options']['htpasswd'])) {
                $index = array_search($adapter, $adapters);
                $adapters[$index] = $adapter . '-basic';
            }
            if (isset($adapterConfig['options']['htdigest'])) {
                $index = array_search($adapter, $adapters);
                $adapters[$index] = $adapter . '-digest';
            }
        }

        $this->assertEquals($adapters, $result->getVariable('auth-types'));
    }

    public function getOldAuthConfig()
    {
        return [
            'basic' => [
                [
                    'api-tools-mvc-auth' => [
                        'authentication' => [
                            'http' => [
                                'accept_schemes' => ['basic'],
                                'realm' => 'My Web Site',
                            ],
                        ],
                    ],
                ],
                [
                    'api-tools-mvc-auth' => [
                        'authentication' => [
                            'http' => [
                                'htpasswd' => __DIR__ . '/TestAsset/Auth2/config/autoload/htpasswd',
                            ],
                        ],
                    ],
                ],
                ['basic'],
            ],
            'digest' => [
                [
                    'api-tools-mvc-auth' => [
                        'authentication' => [
                            'http' => [
                                'accept_schemes' => ['digest'],
                                'realm' => 'My Web Site',
                                'domain_digest' => 'domain.com',
                                'nonce_timeout' => 3600,
                            ],
                        ],
                    ],
                ],
                [
                    'api-tools-mvc-auth' => [
                        'authentication' => [
                            'http' => [
                                'htdigest' => __DIR__ . '/TestAsset/Auth2/config/autoload/htdigest',
                            ],
                        ],
                    ],
                ],
                ['digest'],
            ],
            'oauth2-pdo' => [
                [
                    'router' => [
                        'routes' => [
                            'oauth' => [
                                'options' => [
                                    'route' => '/oauth',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'api-tools-oauth2' => [
                        'storage' => 'Laminas\\ApiTools\\OAuth2\\Adapter\\PdoAdapter',
                        'db' => [
                            'dsn_type'  => 'PDO',
                            'dsn'       => 'sqlite:/' . __DIR__ . '/TestAsset/Auth2/config/autoload/db.sqlite',
                            'username'  => null,
                            'password'  => null,
                        ],
                    ],
                ],
                ['oauth2'],
            ],
            'oauth2-mongo' => [
                [
                    'router' => [
                        'routes' => [
                            'oauth' => [
                                'options' => [
                                    'route' => '/oauth',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'api-tools-oauth2' => [
                        'storage' => 'Laminas\\ApiTools\\OAuth2\\Adapter\\MongoAdapter',
                        'mongo' => [
                            'dsn_type'     => 'Mongo',
                            'dsn'          => 'mongodb://localhost',
                            'database'     => 'api-tools-admin-test',
                            'locator_name' => 'MongoDB',
                        ],
                    ],
                ],
                ['oauth2'],
            ],
        ];
    }

    /**
     * @dataProvider getOldAuthConfig
     */
    public function testGetAuthenticationWithOldConfiguration($global, $local, $expected)
    {
        file_put_contents($this->globalFile, '<' . '?php return '. var_export($global, true) . ';');
        file_put_contents($this->localFile, '<' . '?php return '. var_export($local, true) . ';');

        $controller = $this->getController($this->localFile, $this->globalFile);

        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.api-tools.v2+json');
        $controller->setRequest($request);

        $routeMatch = $this->createRouteMatch([]);
        $routeMatch->setMatchedRouteName('api-tools/api/authentication-type');
        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);
        $controller->setEvent($event);

        $result = $controller->authTypeAction();

        $this->assertInstanceOf('Laminas\ApiTools\ContentNegotiation\ViewModel', $result);

        $types = $result->getVariable('auth-types');
        $this->assertEquals($expected, $types);
    }
}
