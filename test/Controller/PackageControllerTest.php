<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Controller\PackageController;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParam;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParams;
use Laminas\ApiTools\ContentNegotiation\ParameterDataContainer;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;
use Laminas\Mvc\MvcEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function chdir;
use function file_exists;
use function file_get_contents;
use function getcwd;
use function strlen;
use function sys_get_temp_dir;

class PackageControllerTest extends TestCase
{
    use ProphecyTrait;

    /** @var PackageController */
    private $controller;

    public function setUp(): void
    {
        // Seed with symlink path for zfdeploy.php
        $this->controller = new PackageController('vendor/bin/zfdeploy.php');
        $plugins          = new ControllerPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $plugins->setService('bodyParam', new BodyParam());
        $plugins->setService('bodyParams', new BodyParams());
        $this->controller->setPluginManager($plugins);
    }

    /** @psalm-return array<array-key, array{0: string}> */
    public function invalidRequestMethods(): array
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
    public function testProcessWithInvalidRequestMethodReturnsApiProblemResponse(string $method): void
    {
        $request = new Request();
        $request->setMethod($method);
        $this->controller->setRequest($request);
        $result = $this->controller->indexAction();
        self::assertInstanceOf(ApiProblemResponse::class, $result);
        $apiProblem = $result->getApiProblem();
        self::assertEquals(405, $apiProblem->status);
    }

    /** @return array<string, mixed> */
    public function testProcessPostRequestReturnsToken(): array
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

        self::assertIsArray($result);
        self::assertTrue(isset($result['token']));
        self::assertTrue(isset($result['format']));
        $package = sys_get_temp_dir() . '/api-tools_' . $result['token'] . '.' . $result['format'];
        self::assertTrue(file_exists($package));

        return $result;
    }

    /**
     * @depends testProcessPostRequestReturnsToken
     * @param array<string, mixed> $data
     */
    public function testProcessGetRequestReturnsFile(array $data): void
    {
        $request = new Request();
        $request->setMethod('get');
        $request->getQuery()->set('format', $data['format']);
        $request->getQuery()->set('token', $data['token']);

        $this->controller->setRequest($request);

        $package = sys_get_temp_dir() . '/api-tools_' . $data['token'] . '.' . $data['format'];
        $content = file_get_contents($package);

        $response = $this->controller->indexAction();

        self::assertTrue($response->isSuccess());
        self::assertEquals($content, $response->getRawBody());
        self::assertEquals('application/octet-stream', $response->getHeaders()->get('Content-Type')->getFieldValue());
        self::assertEquals(strlen($content), $response->getHeaders()->get('Content-Length')->getFieldValue());

        // Removal of file only happens during destruct
        $this->controller->__destruct();
        self::assertFalse(file_exists($package));
    }
}
