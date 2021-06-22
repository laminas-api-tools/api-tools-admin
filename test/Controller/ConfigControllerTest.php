<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Controller\ConfigController;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParams;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\PluginManager as ControllerPluginManager;
use Laminas\Stdlib\Parameters;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function file_put_contents;
use function json_encode;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

class ConfigControllerTest extends TestCase
{
    use ProphecyTrait;

    /** @var false|string */
    private $file;
    /** @var TestAsset\ConfigWriter */
    private $writer;
    /** @var ConfigController */
    private $controller;
    /** @var ControllerPluginManager */
    private $plugins;

    public function setUp(): void
    {
        $this->file = tempnam(sys_get_temp_dir(), 'laminasconfig');
        file_put_contents($this->file, '<' . "?php\nreturn array();");

        $this->writer     = new TestAsset\ConfigWriter();
        $configResource   = new ConfigResource([], $this->file, $this->writer);
        $this->controller = new ConfigController($configResource);

        $this->plugins = new ControllerPluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $this->plugins->setService('bodyParams', new BodyParams());
        $this->controller->setPluginManager($this->plugins);
    }

    public function tearDown(): void
    {
        unlink($this->file);
    }

    /** @psalm-return array<array-key, array{0: string}> */
    public function invalidRequestMethods(): array
    {
        return [
            ['post'],
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
        $result = $this->controller->processAction();
        self::assertInstanceOf(ApiProblemResponse::class, $result);
        $apiProblem = $result->getApiProblem();
        self::assertEquals(405, $apiProblem->status);
    }

    public function testProcessGetRequestWithLaminasApiToolsMediaTypeReturnsFullConfiguration(): void
    {
        $config         = [
            'foo' => 'FOO',
            'bar' => [
                'baz' => 'bat',
            ],
            'baz' => 'BAZ',
        ];
        $configResource = new ConfigResource($config, $this->file, $this->writer);
        $controller     = new ConfigController($configResource);
        $controller->setPluginManager($this->plugins);

        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/vnd.laminas-api-tools.v1.config+json');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.laminas-api-tools.v1.config+json');
        $controller->setRequest($request);

        $result = $controller->processAction();
        self::assertIsArray($result);
        self::assertEquals($config, $result);
    }

    public function testProcessGetRequestWithGenericJsonMediaTypeReturnsFlattenedConfiguration(): void
    {
        $config         = [
            'foo' => 'FOO',
            'bar' => [
                'baz' => 'bat',
            ],
            'baz' => 'BAZ',
        ];
        $configResource = new ConfigResource($config, $this->file, $this->writer);
        $controller     = new ConfigController($configResource);
        $controller->setPluginManager($this->plugins);

        $request = new Request();
        $request->setMethod('get');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $controller->setRequest($request);

        $result = $controller->processAction();
        self::assertIsArray($result);

        $expected = [
            'foo'     => 'FOO',
            'bar.baz' => 'bat',
            'baz'     => 'BAZ',
        ];
        self::assertEquals($expected, $result);
    }

    public function testProcessPatchRequestWithLaminasApiToolsMediaTypeReturnsUpdatedConfigurationKeys(): void
    {
        $config         = [
            'foo' => 'FOO',
            'bar' => [
                'baz' => 'bat',
            ],
            'baz' => 'BAZ',
        ];
        $configResource = new ConfigResource($config, $this->file, $this->writer);
        $controller     = new ConfigController($configResource);
        $controller->setPluginManager($this->plugins);

        $request = new Request();
        $request->setMethod('patch');
        $request->setContent(json_encode([
            'bar' => [
                'baz' => 'UPDATED',
            ],
            'baz' => 'UPDATED',
        ]));
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/vnd.laminas-api-tools.v1.config+json');
        $request->getHeaders()->addHeaderLine('Accept', 'application/vnd.laminas-api-tools.v1.config+json');
        $controller->setRequest($request);

        $result = $controller->processAction();
        self::assertIsArray($result);

        $expected = [
            'bar' => [
                'baz' => 'UPDATED',
            ],
            'baz' => 'UPDATED',
        ];
        self::assertEquals($expected, $result);
    }

    public function testProcessPatchRequestWithGenericJsonMediaTypeReturnsUpdatedConfigurationKeys(): void
    {
        $config         = [
            'foo' => 'FOO',
            'bar' => [
                'baz' => 'bat',
            ],
            'baz' => 'BAZ',
        ];
        $configResource = new ConfigResource($config, $this->file, $this->writer);
        $controller     = new ConfigController($configResource);
        $controller->setPluginManager($this->plugins);

        $request = new Request();
        $request->setMethod('patch');
        $request->setPost(new Parameters([
            'bar.baz' => 'UPDATED',
            'baz'     => 'UPDATED',
        ]));
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');
        $controller->setRequest($request);

        $result = $controller->processAction();
        self::assertIsArray($result);

        $expected = [
            'bar.baz' => 'UPDATED',
            'baz'     => 'UPDATED',
        ];
        self::assertEquals($expected, $result);
    }
}
