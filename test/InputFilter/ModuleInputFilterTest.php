<?php

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

class ModuleInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => 'Laminas\ApiTools\Admin\InputFilter\ModuleInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'singular-namespace' => [
                ['name' => 'Foo'],
            ],
            'underscore_namespace' => [
                ['name' => 'My_Status'],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'missing-name' => [
                [],
                ['name'],
            ],
            'empty-name' => [
                ['name' => ''],
                ['name'],
            ],
            'underscore-only' => [
                ['name' => '_'],
                ['name'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $expectedMessageKeys)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $messages = $filter->getMessages();
        $messageKeys = array_keys($messages);
        sort($expectedMessageKeys);
        sort($messageKeys);
        $this->assertEquals($expectedMessageKeys, $messageKeys);
    }
}
