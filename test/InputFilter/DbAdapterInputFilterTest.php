<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\Factory;
use PHPUnit_Framework_TestCase as TestCase;

class DbAdapterInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => 'Laminas\ApiTools\Admin\InputFilter\DbAdapterInputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'valid' => [
                [
                    'adapter_name' => 'Db\Status',
                    'database' => '/path/to/foobar',
                    'driver' => 'pdo_sqlite',
                ],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'missing-adapter-name' => [
                [
                    'database' => '/path/to/foobar',
                    'driver' => 'pdo_sqlite',
                ],
                ['adapter_name'],
            ],
            'missing-database' => [
                [
                    'adapter_name' => 'Db\Status',
                    'driver' => 'pdo_sqlite',
                ],
                ['database'],
            ],
            'missing-driver' => [
                [
                    'adapter_name' => 'Db\Status',
                    'database' => '/path/to/foobar',
                ],
                ['driver'],
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
