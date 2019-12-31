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
        return $factory->createInputFilter(array(
            'type' => 'Laminas\ApiTools\Admin\InputFilter\DbAdapterInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'valid' => array(
                array(
                    'adapter_name' => 'Db\Status',
                    'database' => '/path/to/foobar',
                    'driver' => 'pdo_sqlite',
                ),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'missing-adapter-name' => array(
                array(
                    'database' => '/path/to/foobar',
                    'driver' => 'pdo_sqlite',
                ),
                array('adapter_name'),
            ),
            'missing-database' => array(
                array(
                    'adapter_name' => 'Db\Status',
                    'driver' => 'pdo_sqlite',
                ),
                array('database'),
            ),
            'missing-driver' => array(
                array(
                    'adapter_name' => 'Db\Status',
                    'database' => '/path/to/foobar',
                ),
                array('driver'),
            ),
        );
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
