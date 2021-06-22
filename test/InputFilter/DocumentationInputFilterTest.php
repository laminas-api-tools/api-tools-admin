<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\DocumentationInputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

class DocumentationInputFilterTest extends TestCase
{
    public function getInputFilter(): DocumentationInputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => DocumentationInputFilter::class,
        ]);
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, string|array<string, string>>
     * }>
     */
    public function dataProviderIsValid(): array
    {
        return [
            'full-rpc' => [
                [
                    'description' => 'Foobar',
                    'GET'         => [
                        'description' => 'another one',
                        'request'     => 'request doc',
                        'response'    => 'response doc',
                    ],
                    'POST'        => [
                        'description' => 'another one',
                        'request'     => 'request doc',
                        'response'    => 'response doc',
                    ],
                    'PUT'         => [
                        'description' => 'another one',
                        'request'     => 'request doc',
                        'response'    => 'response doc',
                    ],
                    'PATCH'       => [
                        'description' => 'another one',
                        'request'     => 'request doc',
                        'response'    => 'response doc',
                    ],
                    'DELETE'      => [
                        'description' => 'another one',
                        'request'     => 'request doc',
                        'response'    => 'response doc',
                    ],
                ],
            ],
            // full REST
            'full-rest' => [
                [
                    'description' => 'Foobar',
                    'collection'  => [
                        'GET'    => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                        'POST'   => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                        'PUT'    => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                        'PATCH'  => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                        'DELETE' => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                    ],
                    'entity'      => [
                        'GET'    => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                        'POST'   => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                        'PUT'    => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                        'PATCH'  => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                        'DELETE' => [
                            'description' => 'another one',
                            'request'     => 'request doc',
                            'response'    => 'response doc',
                        ],
                    ],
                ],
            ],
            'empty'     => [
                [],
            ],
        ];
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, mixed>,
     *     1: array<string, string[]>
     * }>
     */
    public function dataProviderIsInvalid(): array
    {
        return [
            'invalid-top-level-keys'                              => [
                ['description' => 'foobar', 'Foobar' => 'baz'],
                [
                    'Foobar' => [
                        'An invalid key was encountered in the top position for "Foobar";'
                        . ' must be one of an HTTP method, collection, entity, or description',
                    ],
                ],
            ],
            'collection-or-entity-with-top-level-http-methods'    => [
                ['description' => 'foobar', 'GET' => ['description' => 'foobar'], 'entity' => []],
                [
                    'GET' => [
                        'HTTP methods cannot be present when "collection" or "entity" is also present;'
                        . ' please verify data for "GET"',
                    ],
                ],
            ],
            'http-method-with-bad-format'                         => [
                ['description' => 'foobar', 'GET' => ['description' => 'foobar', 'Foo' => 'bar']],
                [
                    'Foo' => [
                        'Documentable elements must be any or all of description, request or response;'
                        . ' please verify "Foo"',
                    ],
                ],
            ],
            'http-method-not-strings'                             => [
                ['description' => 'foobar', 'GET' => ['description' => 'foobar', 'request' => 500]],
                [
                    'request' => ['Documentable elements must be strings; please verify "request"'],
                ],
            ],
            'http-method-not-strings-in-entity'                   => [
                [
                    'description' => 'foobar',
                    'entity'      => [
                        'GET' => ['description' => 'foobar', 'response' => 500],
                    ],
                ],
                [
                    'response' => ['Documentable elements must be strings; please verify "response"'],
                ],
            ],
            'description-is-not-a-string'                         => [
                ['description' => 5],
                [
                    'description' => [
                        'Description must be provided as a string; please verify description for "description"',
                    ],
                ],
            ],
            'description-is-not-a-string-in-entity-or-collection' => [
                ['collection' => ['description' => 5]],
                [
                    'collection' => [
                        'Description must be provided as a string; please verify description for "description"',
                    ],
                ],
            ],
            'collection-or-entity-not-an-array'                   => [
                ['collection' => 5],
                [
                    'collection' => [
                        'Collections and entities methods must be an array of HTTP methods;'
                        . ' received invalid entry for "collection"',
                    ],
                ],
            ],
            'collection-or-entity-using-wrong-key'                => [
                ['collection' => ['Foo' => 'bar']],
                [
                    'collection' => [
                        'Key must be description or an HTTP indexed list; please verify documentation for "Foo"',
                    ],
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
        $this->assertTrue($filter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid(array $data, array $messages)
    {
        $filter = $this->getInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
