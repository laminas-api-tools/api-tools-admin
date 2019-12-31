<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\Factory;
use PHPUnit_Framework_TestCase as TestCase;

class ModuleInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter(array(
            'type' => 'Laminas\ApiTools\Admin\InputFilter\ModuleInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'singular-namespace' => array(
                array('name' => 'Foo'),
            ),
            'underscore_namespace' => array(
                array('name' => 'My_Status'),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'missing-name' => array(
                array(),
                array('name'),
            ),
            'empty-name' => array(
                array('name' => ''),
                array('name'),
            ),
            'underscore-only' => array(
                array('name' => '_'),
                array('name'),
            ),
            'namespace-separator' => array(
                array('name' => 'My\Status'),
                array('name'),
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
