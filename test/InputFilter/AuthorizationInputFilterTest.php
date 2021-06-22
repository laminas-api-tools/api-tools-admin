<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\AuthorizationInputFilter;
use PHPUnit\Framework\TestCase;

class AuthorizationInputFilterTest extends TestCase
{
    /** @psalm-return array<string, array{0: array<string, array<string, bool>>}> */
    public function dataProviderIsValid(): array
    {
        return [
            'empty' => [
                [],
            ],
            'valid' => [
                [
                    'Foo\V1\Rest\Bar\Controller::__entity__' => ['POST' => true, 'GET' => false],
                    'Foo\V1\Rpc\Boom\Controller::boom'       => ['GET' => true, 'DELETE' => false, 'PATCH' => true],
                ],
            ],
        ];
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, string|array<string, bool>>,
     *     1: array<string, string[]>
     * }>
     */
    public function dataProviderIsInvalid(): array
    {
        return [
            'invalid-controller-name' => [
                [
                    'Foo\V1\Rest\Bar\Controller' => [],
                ],
                [
                    'Foo\V1\Rest\Bar\Controller' => [
                        'Class service name is invalid, must be serviceName::method,'
                        . ' serviceName::__collection__, or serviceName::__entity__',
                    ],
                ],
            ],
            'values-not-array'        => [
                [
                    'Foo\V1\Rest\Bar\Controller::__entity__' => 'GET=true',
                ],
                [
                    'Foo\V1\Rest\Bar\Controller::__entity__' => [
                        'Values for each controller must be an http method keyed array of true/false values',
                    ],
                ],
            ],
            'invalid-http-method'     => [
                [
                    'Foo\V1\Rest\Bar\Controller::__entity__' => ['MYMETHOD' => true],
                ],
                [
                    'Foo\V1\Rest\Bar\Controller::__entity__' => ['Invalid HTTP method (MYMETHOD) provided.'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid(array $data)
    {
        $filter = new AuthorizationInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid(array $data, array $messages)
    {
        $filter = new AuthorizationInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
