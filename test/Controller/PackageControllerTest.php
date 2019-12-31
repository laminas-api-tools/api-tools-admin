<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Controller\PackageController;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParam;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParams;
use Laminas\ApiTools\ContentNegotiation\ParameterDataContainer;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;
use Laminas\Mvc\MvcEvent;
use PHPUnit\Framework\TestCase;

class PackageControllerTest extends TestCase
{
    public function setUp()
    {
        // Seed with symlink path for zfdeploy.php
        $this->controller = new PackageController('vendor/bin/zfdeploy.php');
        $this->plugins = new ControllerPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->plugins->setService('bodyParam', new BodyParam());
        $this->plugins->setService('bodyParams', new BodyParams());
        $this->controller->setPluginManager($this->plugins);
    }

    public function invalidRequestMethods()
    {
        return [
            ['patch'],
            ['put'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemResponse($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $this->controller->setRequest($request);
        $result = $this->controller->indexAction();
        $this->assertInstanceOf('Laminas\ApiTools\ApiProblem\ApiProblemResponse', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }


    public function testProcessPostRequestReturnsToken()
    {
        $request = new Request();
        $request->setMethod('post');

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParam('format', 'ZIP');
        $event = new MvcEvent();
        $event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');

        $this->controller->setRequest($request);
        $this->controller->setEvent($event);

        $cwd = getcwd();
        chdir(__DIR__ . '/TestAsset');
        $result = $this->controller->indexAction();
        chdir($cwd);

        $this->assertInternalType('array', $result);
        $this->assertTrue(isset($result['token']));
        $this->assertTrue(isset($result['format']));
        $package = sys_get_temp_dir() . '/api-tools_' . $result['token'] . '.' . $result['format'];
        $this->assertTrue(file_exists($package));

        return $result;
    }

    /**
     * @depends testProcessPostRequestReturnsToken
     */
    public function testProcessGetRequestReturnsFile(array $data)
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getQuery()->set('format', $data['format']);
        $request->getQuery()->set('token', $data['token']);

        $this->controller->setRequest($request);

        $package = sys_get_temp_dir() . '/api-tools_' . $data['token'] . '.' . $data['format'];
        $content = file_get_contents($package);

        $response = $this->controller->indexAction();

        $this->assertTrue($response->isSuccess());
        $this->assertEquals($content, $response->getRawBody());
        $this->assertEquals('application/octet-stream', $response->getHeaders()->get('Content-Type')->getFieldValue());
        $this->assertEquals(strlen($content), $response->getHeaders()->get('Content-Length')->getFieldValue());

        // Removal of file only happens during destruct
        $this->controller->__destruct();
        $this->assertFalse(file_exists($package));
    }
}
