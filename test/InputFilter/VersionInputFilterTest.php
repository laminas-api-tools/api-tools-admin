<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\Factory;
use PHPUnit_Framework_TestCase as TestCase;

class VersionInputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory();
        return $factory->createInputFilter(array(
            'type' => 'Laminas\ApiTools\Admin\InputFilter\VersionInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'valid' => array(
                array(
                    'module' => 'foo',
                    'version' => 5,
                ),
            ),
            'version-with-alphas' => array(
                array(
                    'module' => 'foo',
                    'version' => 'alpha',
                ),
            ),
            'version-with-mixed' => array(
                array(
                    'module' => 'foo',
                    'version' => 'alpha_1',
                ),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'empty' => array(
                array(),
                array('module', 'version'),
            ),
            'missing-module' => array(
                array('version' => 'foo'),
                array('module'),
            ),
            'missing-version' => array(
                array('module' => 'foo'),
                array('version'),
            ),
            'version-with-spaces' => array(
                array('module' => 'foo', 'version' => 'foo bar'),
                array('version'),
            ),
            'version-with-dashes' => array(
                array('module' => 'foo', 'version' => 'foo-bar'),
                array('version'),
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
