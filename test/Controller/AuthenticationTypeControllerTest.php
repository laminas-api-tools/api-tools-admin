<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Controller\AuthenticationTypeController;
use Laminas\ApiTools\MvcAuth\Authentication\DefaultAuthenticationListener as AuthListener;
use Laminas\Http\Request;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase as TestCase;

class AuthenticationTypeControllerTest extends TestCase
{
    public function setUp()
    {
        $this->globalFile = __DIR__ . '/TestAsset/Auth2/config/autoload/global.php';
        $this->localFile  = __DIR__ . '/TestAsset/Auth2/config/autoload/local.php';
        copy($this->globalFile . '.dist', $this->globalFile);
        copy($this->localFile . '.dist', $this->localFile);

        $this->controller = $this->getController($this->localFile, $this->globalFile);

        $this->routeMatch = new RouteMatch(array());
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
            $authListener->addAuthenticationTypes(array('oauth2'));
        } elseif (isset($config['api-tools-mvc-auth']['authentication']['http'])) {
            $types = array();
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
                        $authListener->addAuthenticationTypes(array($adapter . '-' . 'basic'));
                    }
                    if (isset($adapterConfig['options']['htdigest'])) {
                        $authListener->addAuthenticationTypes(array($adapter . '-' . 'digest'));
                    }
                    continue;
                }
                $authListener->addAuthenticationTypes(array($adapter));
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
        return array(
            array('post', 'put', 'patch', 'delete')
        );
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
        return array(
            'basic' => array(
                array(
                    'api-tools-mvc-auth' => array(
                        'authentication' => array(
                            'http' => array(
                                'accept_schemes' => array('basic'),
                                'realm' => 'My Web Site',
                            ),
                        ),
                    ),
                ),
                array(
                    'api-tools-mvc-auth' => array(
                        'authentication' => array(
                            'http' => array(
                                'htpasswd' => __DIR__ . '/TestAsset/Auth2/config/autoload/htpasswd',
                            ),
                        ),
                    ),
                ),
                array('basic'),
            ),
            'digest' => array(
                array(
                    'api-tools-mvc-auth' => array(
                        'authentication' => array(
                            'http' => array(
                                'accept_schemes' => array('digest'),
                                'realm' => 'My Web Site',
                                'domain_digest' => 'domain.com',
                                'nonce_timeout' => 3600,
                            ),
                        ),
                    ),
                ),
                array(
                    'api-tools-mvc-auth' => array(
                        'authentication' => array(
                            'http' => array(
                                'htdigest' => __DIR__ . '/TestAsset/Auth2/config/autoload/htdigest',
                            ),
                        ),
                    ),
                ),
                array('digest'),
            ),
            'oauth2-pdo' => array(
                array(
                    'router' => array(
                        'routes' => array(
                            'oauth' => array(
                                'options' => array(
                                    'route' => '/oauth',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'api-tools-oauth2' => array(
                        'storage' => 'Laminas\\ApiTools\\OAuth2\\Adapter\\PdoAdapter',
                        'db' => array(
                            'dsn_type'  => 'PDO',
                            'dsn'       => 'sqlite:/' . __DIR__ . '/TestAsset/Auth2/config/autoload/db.sqlite',
                            'username'  => null,
                            'password'  => null,
                        ),
                    ),
                ),
                array('oauth2'),
            ),
            'oauth2-mongo' => array(
                array(
                    'router' => array(
                        'routes' => array(
                            'oauth' => array(
                                'options' => array(
                                    'route' => '/oauth',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'api-tools-oauth2' => array(
                        'storage' => 'Laminas\\ApiTools\\OAuth2\\Adapter\\MongoAdapter',
                        'mongo' => array(
                            'dsn_type'     => 'Mongo',
                            'dsn'          => 'mongodb://localhost',
                            'database'     => 'api-tools-admin-test',
                            'locator_name' => 'MongoDB',
                        ),
                    ),
                ),
                array('oauth2'),
            ),
        );
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

        $routeMatch = new RouteMatch(array());
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
