<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter\RestService;

use Laminas\InputFilter\Factory;
use PHPUnit_Framework_TestCase as TestCase;

class PostInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => 'Laminas\ApiTools\Admin\InputFilter\RestService\PostInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'code-connected' => [['service_name' => 'Foo']],
            'db-connected'   => [['adapter_name' => 'Status', 'table_name' => 'foo']],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            // no values
            'empty' => [
                [],
                ['service_name'],
            ],
            // invalid service_name
            'invalid-service-name' => [
                ['service_name' => '_'],
                ['service_name'],
            ],
            // adapter without table
            'valid-adapter-missing-table' => [
                ['adapter_name' => 'Foo'],
                ['table_name'],
            ],
            // table without adapter
            'missing-adapter-valid-table' => [
                ['table_name' => 'Foo'],
                ['adapter_name'],
            ],
            // both present
            'conflict' => [
                ['service_name' => 'Foo', 'adapter_name' => 'bar'],
                ['service_name'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValidTrue($data)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsValidFalse($data, $expectedValidationKeys)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());

        $messages = $filter->getMessages();
        $messages = array_keys($messages);
        sort($expectedValidationKeys);
        sort($messages);
        $this->assertEquals($expectedValidationKeys, $messages);
    }
}
