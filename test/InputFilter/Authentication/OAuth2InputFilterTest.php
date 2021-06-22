<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter\Authentication;

use Laminas\ApiTools\Admin\InputFilter\Authentication\OAuth2InputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function sort;
use function var_export;

class OAuth2InputFilterTest extends TestCase
{
    public function getInputFilter(): OAuth2InputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => OAuth2InputFilter::class,
        ]);
    }

    /** @psalm-return array<string, array{0: array<string, string>}> */
    public function dataProviderIsValid(): array
    {
        return [
            'minimal'           => [
                [
                    'dsn'         => 'sqlite://:memory:',
                    'dsn_type'    => 'PDO',
                    'route_match' => '/foo',
                ],
            ],
            'full'              => [
                [
                    'dsn'         => 'sqlite://:memory:',
                    'dsn_type'    => 'PDO',
                    'password'    => 'foobar',
                    'route_match' => '/foo',
                    'username'    => 'barfoo',
                ],
            ],
            'mongo-without-dsn' => [
                [
                    'dsn_type'    => 'Mongo',
                    'database'    => 'oauth2',
                    'route_match' => '/oauth2',
                ],
            ],
        ];
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, string>,
     *     1: string[]
     * }>
     */
    public function dataProviderIsInvalid(): array
    {
        return [
            'empty'                  => [
                [],
                [
                    'dsn',
                    'dsn_type',
                    'route_match',
                ],
            ],
            'empty-values'           => [
                [
                    'dsn'         => '',
                    'dsn_type'    => '',
                    'password'    => '',
                    'route_match' => '',
                    'username'    => '',
                ],
                [
                    'dsn',
                    'dsn_type',
                    'route_match',
                ],
            ],
            'mongo-without-database' => [
                [
                    'dsn_type'    => 'Mongo',
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
    public function testIsValid(array $data)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), true));
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
