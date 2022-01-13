<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter\RestService;

use Laminas\ApiTools\Admin\InputFilter\RestService\PatchInputFilter;
use Laminas\Hydrator\ArraySerializableHydrator;
use Laminas\InputFilter\Factory;
use Laminas\Paginator\Paginator;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function sort;
use function var_export;

class PatchInputFilterTest extends TestCase
{
    public function getInputFilter(): PatchInputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => PatchInputFilter::class,
        ]);
    }

    /** @psalm-return array<string, array{0: array<string, mixed>}> */
    public function dataProviderIsValidTrue(): array
    {
        return [
            'all-inputs-present' => [
                [
                    'accept_whitelist'           => [
                        0 => 'application/vnd.foo_bar.v1+json',
                        1 => 'application/hal+json',
                        2 => 'application/json',
                    ],
                    'collection_class'           => Paginator::class,
                    'collection_http_methods'    => [
                        0 => 'GET',
                        1 => 'POST',
                    ],
                    'collection_name'            => 'foo_bar',
                    'collection_query_whitelist' => [],
                    'content_type_whitelist'     => [
                        0 => 'application/vnd.foo_bar.v1+json',
                        1 => 'application/json',
                    ],
                    'entity_class'               => 'StdClass',
                    'entity_http_methods'        => [
                        0 => 'GET',
                        1 => 'PATCH',
                        2 => 'PUT',
                        3 => 'DELETE',
                    ],
                    'entity_identifier_name'     => 'id',
                    'hydrator_name'              => ArraySerializableHydrator::class,
                    'page_size'                  => "25",
                    'page_size_param'            => null,
                    'resource_class'             => 'Foo_Bar\\V1\\Rest\\Baz_Bat\\Baz_BatResource',
                    'route_identifier_name'      => 'foo_bar_id',
                    'route_match'                => '/foo_bar[/:foo_bar_id]',
                    'selector'                   => 'HalJson',
                    'service_name'               => 'Baz_Bat',
                ],
            ],
            'page_size-negative' => [
                [
                    'accept_whitelist'           => [
                        0 => 'application/vnd.foo_bar.v1+json',
                        1 => 'application/hal+json',
                        2 => 'application/json',
                    ],
                    'collection_class'           => Paginator::class,
                    'collection_http_methods'    => [
                        0 => 'GET',
                        1 => 'POST',
                    ],
                    'collection_name'            => 'foo_bar',
                    'collection_query_whitelist' => [],
                    'content_type_whitelist'     => [
                        0 => 'application/vnd.foo_bar.v1+json',
                        1 => 'application/json',
                    ],
                    'entity_class'               => 'StdClass',
                    'entity_http_methods'        => [
                        0 => 'GET',
                        1 => 'PATCH',
                        2 => 'PUT',
                        3 => 'DELETE',
                    ],
                    'entity_identifier_name'     => 'id',
                    'hydrator_name'              => ArraySerializableHydrator::class,
                    'page_size'                  => -1,
                    'page_size_param'            => null,
                    'resource_class'             => 'Foo_Bar\\V1\\Rest\\Baz_Bat\\Baz_BatResource',
                    'route_identifier_name'      => 'foo_bar_id',
                    'route_match'                => '/foo_bar[/:foo_bar_id]',
                    'selector'                   => 'HalJson',
                    'service_name'               => 'Baz_Bat',
                ],
            ],
        ];
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, mixed>,
     *     1: string[]
     * }>
     */
    public function dataProviderIsValidFalse(): array
    {
        return [
            'missing-service-name' => [
                [
                    'accept_whitelist'           => [
                        0 => 'application/vnd.foo_bar.v1+json',
                        1 => 'application/hal+json',
                        2 => 'application/json',
                    ],
                    'collection_class'           => null,
                    'collection_http_methods'    => [
                        0 => 'GET',
                        1 => 'POST',
                    ],
                    'collection_query_whitelist' => [],
                    'content_type_whitelist'     => [
                        0 => 'application/vnd.foo_bar.v1+json',
                        1 => 'application/json',
                    ],
                    'entity_class'               => null,
                    'entity_http_methods'        => [
                        0 => 'GET',
                        1 => 'PATCH',
                        2 => 'PUT',
                        3 => 'DELETE',
                    ],
                    'hydrator_name'              => null,
                    'page_size'                  => null,
                    'page_size_param'            => null,
                    'resource_class'             => null,
                    'route_match'                => null,
                    'selector'                   => null,
                ],
                [
                    'service_name',
                ],
            ],
            'empty-inputs'         => [
                [
                    'accept_whitelist'           => [
                        0 => 'application/vnd.foo_bar.v1+json',
                        1 => 'application/hal+json',
                        2 => 'application/json',
                    ],
                    'collection_class'           => null,
                    'collection_http_methods'    => [
                        0 => 'GET',
                        1 => 'POST',
                    ],
                    'collection_name'            => null,
                    'collection_query_whitelist' => [],
                    'content_type_whitelist'     => [
                        0 => 'application/vnd.foo_bar.v1+json',
                        1 => 'application/json',
                    ],
                    'entity_class'               => null,
                    'entity_http_methods'        => [
                        0 => 'GET',
                        1 => 'PATCH',
                        2 => 'PUT',
                        3 => 'DELETE',
                    ],
                    'entity_identifier_name'     => null,
                    'hydrator_name'              => null,
                    'page_size'                  => null,
                    'page_size_param'            => null,
                    'resource_class'             => null,
                    'route_identifier_name'      => null,
                    'route_match'                => null,
                    'selector'                   => null,
                    'service_name'               => 'Foo_Bar',
                ],
                [
                    'collection_class',
                    'collection_name',
                    'entity_class',
                    'entity_identifier_name',
                    'page_size',
                    // 'resource_class', // Resource class is allowed to be empty
                    'route_identifier_name',
                    'route_match',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValidTrue
     * @param array<string, mixed> $data
     */
    public function testIsValidTrue(array $data): void
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        self::assertTrue($filter->isValid(), var_export($filter->getMessages(), true));
    }

    /**
     * @dataProvider dataProviderIsValidFalse
     * @param array<string, mixed> $data
     * @param string[] $expectedInvalidKeys
     */
    public function testIsValidFalse(array $data, array $expectedInvalidKeys): void
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        self::assertFalse($filter->isValid());

        $messages = $filter->getMessages();
        $testKeys = array_keys($messages);
        sort($expectedInvalidKeys);
        sort($testKeys);
        self::assertEquals($expectedInvalidKeys, $testKeys);
    }
}
