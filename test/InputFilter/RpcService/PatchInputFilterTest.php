<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter\RpcService;

use Laminas\ApiTools\Admin\InputFilter\RpcService\PatchInputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function sort;

class PatchInputFilterTest extends TestCase
{
    public function getInputFilter(): PatchInputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => PatchInputFilter::class,
        ]);
    }

    /** @psalm-return array<string, array{0: array<string, mixed>, 1: string[]}> */
    public function dataProviderIsValid(): array
    {
        return [
            [
                [
                    'service_name'           => 'Foo',
                    'route_match'            => '/foo',
                    'controller_class'       => 'FooBar\V1\Rpc\Foo\FooController',
                    'accept_whitelist'       => [
                        'application/vnd.foo.v1+json',
                        'application/hal+json',
                        'application/json',
                    ],
                    'content_type_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/json',
                    ],
                    'selector'               => 'HalJson',
                    'http_methods'           => ['GET', 'POST', 'PATCH'],
                ],
            ],
        ];
    }

    /** @psalm-return array<string, array{0: array<string, mixed>, 1: string[]}> */
    public function dataProviderIsInvalid(): array
    {
        return [
            'missing-service-name' => [
                [
                    'route_match'            => '/foo',
                    'controller_class'       => 'FooBar\V1\Rpc\Foo\FooController',
                    'accept_whitelist'       => [
                        'application/vnd.foo.v1+json',
                        'application/hal+json',
                        'application/json',
                    ],
                    'content_type_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/json',
                    ],
                    'selector'               => 'HalJson',
                    'http_methods'           => ['GET', 'POST', 'PATCH'],
                ],
                ['service_name'],
            ],
            'null-values'          => [
                [
                    'service_name'           => 'Foo',
                    'route_match'            => null,
                    'controller_class'       => null,
                    'accept_whitelist'       => [
                        'application/vnd.foo.v1+json',
                        'application/hal+json',
                        'application/json',
                    ],
                    'content_type_whitelist' => [
                        'application/vnd.foo.v1+json',
                        'application/json',
                    ],
                    'selector'               => null,
                    'http_methods'           => ['GET', 'POST', 'PATCH'],
                ],
                ['route_match', 'controller_class'],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     * @param array<string, mixed> $data
     */
    public function testIsValid(array $data): void
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        self::assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     * @param array<string, mixed> $data
     * @param string[] $expectedMessageKeys
     */
    public function testIsInvalid(array $data, array $expectedMessageKeys): void
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        self::assertFalse($filter->isValid());

        $messages = $filter->getMessages();
        $messages = array_keys($messages);
        sort($expectedMessageKeys);
        sort($messages);
        self::assertEquals($expectedMessageKeys, $messages);
    }
}
