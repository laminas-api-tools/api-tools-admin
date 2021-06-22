<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Controller;

use Foo\Module;
use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Controller\ModuleCreationController;
use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ContentNegotiation\ControllerPlugin\BodyParam;
use Laminas\ApiTools\ContentNegotiation\ParameterDataContainer;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\Hal\Entity;
use Laminas\Http\Request;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Mvc\MvcEvent;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

use function array_diff;
use function chdir;
use function file_put_contents;
use function getcwd;
use function is_dir;
use function mkdir;
use function rmdir;
use function scandir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class ModuleCreationControllerTest extends TestCase
{
    use ProphecyTrait;

    /** @var ModuleCreationController */
    private $controller;

    public function setUp(): void
    {
        $moduleManager    = new ModuleManager([]);
        $moduleResource   = new ModuleModel(
            $moduleManager,
            [],
            []
        );
        $this->controller = new ModuleCreationController($moduleResource);
    }

    /** @psalm-return array<array-key, array{0: string}> */
    public function invalidRequestMethods(): array
    {
        return [
            ['get'],
            ['patch'],
            ['post'],
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
        $result = $this->controller->apiEnableAction();
        self::assertInstanceOf(ApiProblemResponse::class, $result);
        $apiProblem = $result->getApiProblem();
        self::assertEquals(405, $apiProblem->status);
    }

    public function testProcessPutRequest(): void
    {
        $currentDir = getcwd();
        $tmpDir     = sys_get_temp_dir() . "/" . uniqid(__NAMESPACE__ . '_');

        mkdir($tmpDir);
        mkdir("$tmpDir/module/Foo", 0775, true);
        mkdir("$tmpDir/config");
        file_put_contents(
            "$tmpDir/config/application.config.php",
            '<' . '?php return array(\'modules\'=>array(\'Foo\'));'
        );
        file_put_contents("$tmpDir/module/Foo/Module.php", "<" . "?php\n\nnamespace Foo;\n\nclass Module\n{\n}");
        chdir($tmpDir);

        require 'module/Foo/Module.php';

        $moduleManager = $this->getMockBuilder(ModuleManager::class)
                               ->disableOriginalConstructor()
                               ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue(['Foo' => new Module()]));

        $moduleResource = new ModuleModel(
            $moduleManager,
            [],
            []
        );
        $controller     = new ModuleCreationController($moduleResource);

        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParam('module', 'Foo');
        $event = new MvcEvent();
        $event->setParam('LaminasContentNegotiationParameterData', $parameters);

        $plugins = new PluginManager($this->prophesize(ContainerInterface::class)->reveal());
        $plugins->setInvokableClass('bodyParam', BodyParam::class);

        $controller->setRequest($request);
        $controller->setEvent($event);
        $controller->setPluginManager($plugins);

        $result = $controller->apiEnableAction();

        self::assertInstanceOf(ViewModel::class, $result);
        $payload = $result->getVariable('payload');
        self::assertInstanceOf(Entity::class, $payload);
        self::assertInstanceOf(ModuleEntity::class, $payload->getEntity());

        $metadata = $payload->getEntity();
        self::assertEquals('Foo', $metadata->getName());

        $this->removeDir($tmpDir);
        chdir($currentDir);
    }

    protected function removeDir(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            is_dir("$dir/$file") ? $this->removeDir("$dir/$file") : unlink("$dir/$file");
        }
        @rmdir($dir);
    }
}
