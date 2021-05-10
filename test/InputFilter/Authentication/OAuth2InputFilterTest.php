<?php

namespace LaminasTest\ApiTools\Admin\InputFilter\Authentication;

use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

class OAuth2InputFilterTest extends TestCase
{
    public function getInputFilter()
    {
        $factory = new Factory;
        return $factory->createInputFilter([
            'type' => 'Laminas\ApiTools\Admin\InputFilter\Authentication\OAuth2InputFilter',
        ]);
    }

    public function dataProviderIsValid()
    {
        return [
            'minimal' => [
                [
                    'dsn' => 'sqlite://:memory:',
                    'dsn_type' => 'PDO',
                    'route_match' => '/foo',
                ],
            ],
            'full' => [
                [
                    'dsn' => 'sqlite://:memory:',
                    'dsn_type' => 'PDO',
                    'password' => 'foobar',
                    'route_match' => '/foo',
                    'username' => 'barfoo',
                ],
            ],
            'mongo-without-dsn' => [
                [
                    'dsn_type' => 'Mongo',
                    'database' => 'oauth2',
                    'route_match' => '/oauth2',
                ],
            ],
        ];
    }

    public function dataProviderIsInvalid()
    {
        return [
            'empty' => [
                [],
                [
                    'dsn',
                    'dsn_type',
                    'route_match',
                ],
            ],
            'empty-values' => [
                [
                    'dsn' => '',
                    'dsn_type' => '',
                    'password' => '',
                    'route_match' => '',
                    'username' => '',
                ],
                [
                    'dsn',
                    'dsn_type',
                    'route_match',
                ],
            ],
            'mongo-without-database' => [
                [
                    'dsn_type' => 'Mongo',
                    'route_match' => '/oauth2',
                ],
                [
                    'database',
                ],
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
