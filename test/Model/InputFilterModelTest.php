<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Model;

use InputFilter\Module;
use Laminas\ApiTools\Admin\Model\InputFilterCollection;
use Laminas\ApiTools\Admin\Model\InputFilterEntity;
use Laminas\ApiTools\Admin\Model\InputFilterModel;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;
use Laminas\Config\Writer\PhpArray;
use Laminas\ModuleManager\ModuleManager;
use PHPUnit\Framework\TestCase;

use function copy;
use function count;
use function sprintf;
use function unlink;
use function var_export;

class InputFilterModelTest extends TestCase
{
    /** @var InputFilterModel */
    private $model;
    /** @var string */
    private $basePath;
    /** @var mixed */
    private $config;

    public function setUp(): void
    {
        $modules = [
            'InputFilter' => new Module(),
        ];

        $moduleManager = $this->getMockBuilder(ModuleManager::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $writer        = new PhpArray();
        $moduleUtils   = new ModuleUtils($moduleManager);
        $configFactory = new ConfigResourceFactory($moduleUtils, $writer);
        $this->model   = new InputFilterModel($configFactory);

        $this->basePath = __DIR__ . '/TestAsset/module/InputFilter/config';
        $this->config   = include $this->basePath . '/module.config.php';

        copy($this->basePath . '/module.config.php', $this->basePath . '/module.config.php.old');
    }

    public function tearDown(): void
    {
        copy($this->basePath . '/module.config.php.old', $this->basePath . '/module.config.php');
        unlink($this->basePath . '/module.config.php.old');
    }

    public function testFetch(): void
    {
        $result = $this->model->fetch('InputFilter', 'InputFilter\V1\Rest\Foo\Controller');
        self::assertInstanceOf(InputFilterCollection::class, $result);
        self::assertEquals(1, count($result));
        $inputFilter = $result->dequeue();
        self::assertInstanceOf(InputFilterEntity::class, $inputFilter);
        self::assertEquals(
            $this->config['input_filter_specs']['InputFilter\V1\Rest\Foo\Validator']['foo'],
            $inputFilter['foo']
        );
    }

    public function testAddInputFilterExistingController(): void
    {
        $inputFilter = [
            'bar' => [
                'name'        => 'bar',
                'required'    => true,
                'allow_empty' => true,
                'validators'  => [
                    [
                        'name' => 'NotEmpty',
                    ],
                ],
            ],
        ];
        $result      = $this->model->update('InputFilter', 'InputFilter\V1\Rest\Foo\Controller', $inputFilter);
        self::assertInstanceOf(InputFilterEntity::class, $result);
        self::assertEquals(
            $inputFilter['bar'],
            $result['bar'],
            sprintf("Updates: %s\n\nResult: %s\n", var_export($inputFilter, true), var_export($result, true))
        );
    }

    public function testAddInputFilterNewController(): void
    {
        $inputFilter = [
            'bar' => [
                'name'        => 'bar',
                'required'    => true,
                'allow_empty' => true,
                'validators'  => [
                    [
                        'name' => 'NotEmpty',
                    ],
                ],
            ],
        ];

        // new controller
        $controller = 'InputFilter\V1\Rest\Bar\Controller';
        $result     = $this->model->update('InputFilter', $controller, $inputFilter);
        self::assertInstanceOf(InputFilterEntity::class, $result);
        self::assertEquals($inputFilter['bar'], $result['bar']);

        $config = include $this->basePath . '/module.config.php';
        self::assertEquals(
            'InputFilter\V1\Rest\Bar\Validator',
            $config['api-tools-content-validation'][$controller]['input_filter']
        );
    }

    public function testRemoveInputFilter(): void
    {
        self::assertTrue($this->model->remove(
            'InputFilter',
            'InputFilter\V1\Rest\Foo\Controller',
            'InputFilter\V1\Rest\Foo\Validator'
        ));
    }

    public function testModuleExists(): void
    {
        self::assertTrue($this->model->moduleExists('InputFilter'));
    }

    public function testControllerExists()
    {
        self::assertTrue($this->model->controllerExists('InputFilter', 'InputFilter\V1\Rest\Foo\Controller'));
    }
}
