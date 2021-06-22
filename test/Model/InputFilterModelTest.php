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
    public function setUp()
    {
        $modules = [
            'InputFilter' => new Module(),
        ];

        $this->moduleManager = $this->getMockBuilder(ModuleManager::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();

        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer        = new PhpArray();
        $moduleUtils         = new ModuleUtils($this->moduleManager);
        $this->configFactory = new ConfigResourceFactory($moduleUtils, $this->writer);
        $this->model         = new InputFilterModel($this->configFactory);

        $this->basePath = __DIR__ . '/TestAsset/module/InputFilter/config';
        $this->config   = include $this->basePath . '/module.config.php';

        copy($this->basePath . '/module.config.php', $this->basePath . '/module.config.php.old');
    }

    public function tearDown()
    {
        copy($this->basePath . '/module.config.php.old', $this->basePath . '/module.config.php');
        unlink($this->basePath . '/module.config.php.old');
    }

    public function testFetch()
    {
        $result = $this->model->fetch('InputFilter', 'InputFilter\V1\Rest\Foo\Controller');
        $this->assertInstanceOf(InputFilterCollection::class, $result);
        $this->assertEquals(1, count($result));
        $inputFilter = $result->dequeue();
        $this->assertInstanceOf(InputFilterEntity::class, $inputFilter);
        $this->assertEquals(
            $this->config['input_filter_specs']['InputFilter\V1\Rest\Foo\Validator']['foo'],
            $inputFilter['foo']
        );
    }

    public function testAddInputFilterExistingController()
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
        $this->assertInstanceOf(InputFilterEntity::class, $result);
        $this->assertEquals(
            $inputFilter['bar'],
            $result['bar'],
            sprintf("Updates: %s\n\nResult: %s\n", var_export($inputFilter, true), var_export($result, true))
        );
    }

    public function testAddInputFilterNewController()
    {
        $inputfilter = [
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
        $result     = $this->model->update('InputFilter', $controller, $inputfilter);
        $this->assertInstanceOf(InputFilterEntity::class, $result);
        $this->assertEquals($inputfilter['bar'], $result['bar']);

        $config = include $this->basePath . '/module.config.php';
        $this->assertEquals(
            'InputFilter\V1\Rest\Bar\Validator',
            $config['api-tools-content-validation'][$controller]['input_filter']
        );
    }

    public function testRemoveInputFilter()
    {
        $this->assertTrue($this->model->remove(
            'InputFilter',
            'InputFilter\V1\Rest\Foo\Controller',
            'InputFilter\V1\Rest\Foo\Validator'
        ));
    }

    public function testModuleExists()
    {
        $this->assertTrue($this->model->moduleExists('InputFilter'));
    }

    public function testControllerExists()
    {
        $this->assertTrue($this->model->controllerExists('InputFilter', 'InputFilter\V1\Rest\Foo\Controller'));
    }
}
