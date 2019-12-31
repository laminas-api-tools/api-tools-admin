<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter\Authentication;

use Laminas\InputFilter\Factory;
use PHPUnit_Framework_TestCase as TestCase;

class BasicInputFilterTest extends TestCase
{
    public function setUp()
    {
        $this->htpasswd = sys_get_temp_dir() . '/' . uniqid() . '.htpasswd';
        touch($this->htpasswd);
    }

    public function tearDown()
    {
        unlink($this->htpasswd);
    }

    public function getInputFilter()
    {
        $factory = new Factory;
        return $factory->createInputFilter(array(
            'type' => 'Laminas\ApiTools\Admin\InputFilter\Authentication\BasicInputFilter',
        ));
    }

    public function dataProviderIsValid()
    {
        return array(
            'basic-only' => array(
                array('accept_schemes' => array('basic'), 'realm' => 'My Realm')
            ),
            'basic-and-digest' => array(
                array('accept_schemes' => array('digest', 'basic'), 'realm' => 'My Realm')
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'empty' => array(
                array(),
                array(
                    'accept_schemes',
                    'realm',
                    'htpasswd',
                ),
            ),
            'empty-data' => array(
                array('accept_schemes' => '', 'realm' => '', 'htpasswd' => ''),
                array(
                    'accept_schemes',
                    'realm',
                    'htpasswd',
                ),
            ),
            'invalid-htpasswd' => array(
                array('accept_schemes' => array('basic'), 'realm' => 'api', 'htpasswd' => '/foo/bar/baz/bat.htpasswd'),
                array(
                    'htpasswd',
                ),
            ),
        );
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $data['htpasswd'] = $this->htpasswd;
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), 1));
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
