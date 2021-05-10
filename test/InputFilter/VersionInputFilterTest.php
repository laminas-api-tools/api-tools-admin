<?php

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

class VersionInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => 'Laminas\ApiTools\Admin\InputFilter\VersionInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'valid' => [
                [
                    'module' => 'foo',
                    'version' => 5,
                ],
            ],
            'version-with-alphas' => [
                [
                    'module' => 'foo',
                    'version' => 'alpha',
                ],
            ],
            'version-with-mixed' => [
                [
                    'module' => 'foo',
                    'version' => 'alpha_1',
                ],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'empty' => [
                [],
                ['module', 'version'],
            ],
            'missing-module' => [
                ['version' => 'foo'],
                ['module'],
            ],
            'missing-version' => [
                ['module' => 'foo'],
                ['version'],
            ],
            'version-with-spaces' => [
                ['module' => 'foo', 'version' => 'foo bar'],
                ['version'],
            ],
            'version-with-dashes' => [
                ['module' => 'foo', 'version' => 'foo-bar'],
                ['version'],
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
