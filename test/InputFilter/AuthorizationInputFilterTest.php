<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\AuthorizationInputFilter;
use PHPUnit_Framework_TestCase as TestCase;

class AuthorizationInputFilterTest extends TestCase
{
    public function dataProviderIsValid()
    {
        return array(
            'empty' => array(
                array(),
            ),
            'valid' => array(
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => array('POST' => true, 'GET' => false),
                    'Foo\V1\Rpc\Boom\Controller::boom' => array('GET' => true, 'DELETE' => false, 'PATCH' => true),
                ),
            ),
        );
    }

    public function dataProviderIsInvalid()
    {
        return array(
            'invalid-controller-name' => array(
                array(
                    'Foo\V1\Rest\Bar\Controller' => array(),
                ),
                array(
                    'Foo\V1\Rest\Bar\Controller' => array('Class service name is invalid, must be serviceName::method, serviceName::__collection__, or serviceName::__entity__'),
                ),
            ),
            'values-not-array' => array(
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => 'GET=true',
                ),
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => array('Values for each controller must be an http method keyed array of true/false values'),
                ),
            ),
            'invalid-http-method' => array(
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => array('MYMETHOD' => true),
                ),
                array(
                    'Foo\V1\Rest\Bar\Controller::__entity__' => array('Invalid HTTP method (MYMETHOD) provided.'),
                ),
            ),
        );
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($data)
    {
        $filter = new AuthorizationInputFilter;
        $filter->setData($data);
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid($data, $messages)
    {
        $filter = new AuthorizationInputFilter;
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
