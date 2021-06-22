<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\ModuleInputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function sort;

class ModuleInputFilterTest extends TestCase
{
    public function getInputFilter(): ModuleInputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => ModuleInputFilter::class,
        ]);
    }

    /** @psalm-return array<string, array{0: array<string, string>}> */
    public function dataProviderIsValid(): array
    {
        return [
            'singular-namespace'   => [
                ['name' => 'Foo'],
            ],
            'underscore_namespace' => [
                ['name' => 'My_Status'],
            ],
        ];
    }

    /** @psalm-return array<string, array{0: array<string, string>, 1: string[]> */
    public function dataProviderIsInvalid(): array
    {
        return [
            'missing-name'    => [
                [],
                ['name'],
            ],
            'empty-name'      => [
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
    public function testIsValid(array $data)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid(array $data, array $expectedMessageKeys)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $messages    = $filter->getMessages();
        $messageKeys = array_keys($messages);
        sort($expectedMessageKeys);
        sort($messageKeys);
        $this->assertEquals($expectedMessageKeys, $messageKeys);
    }
}
