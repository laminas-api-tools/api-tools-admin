<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\CreateContentNegotiationInputFilter;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;

use function var_export;

class CreateContentNegotiationInputFilterTest extends TestCase
{
    /** @psalm-return array<string, array{0: array<string, mixed>}> */
    public function dataProviderIsValid(): array
    {
        return [
            'content-name-only'          => [
                [
                    'content_name' => 'test',
                ],
            ],
            'content-name-and-selectors' => [
                [
                    'content_name' => 'test',
                    'selectors'    => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @psalm-return array<string, array{
     *     0: array{
     *         selectors: array<string, list<string>>,
     *         content_name?: mixed
     *     },
     *     1: array{
     *         content_name: array<string, string>
     *     }
     * }>
     */
    public function dataProviderIsInvalid(): array
    {
        return [
            'missing-content-name' => [
                [
                    'selectors' => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ],
                ],
            ],
            'null-content-name'    => [
                [
                    'content_name' => null,
                    'selectors'    => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'isEmpty' => 'Value is required and can\'t be empty',
                    ],
                ],
            ],
            'bool-content-name'    => [
                [
                    'content_name' => true,
                    'selectors'    => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'invalidType' => 'Value must be a string; received boolean',
                    ],
                ],
            ],
            'int-content-name'     => [
                [
                    'content_name' => 1,
                    'selectors'    => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'invalidType' => 'Value must be a string; received integer',
                    ],
                ],
            ],
            'float-content-name'   => [
                [
                    'content_name' => 1.1,
                    'selectors'    => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'invalidType' => 'Value must be a string; received double',
                    ],
                ],
            ],
            'array-content-name'   => [
                [
                    'content_name' => ['content_name'],
                    'selectors'    => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'invalidType' => 'Value must be a string; received array',
                    ],
                ],
            ],
            'object-content-name'  => [
                [
                    'content_name' => (object) ['content_name'],
                    'selectors'    => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'content_name' => [
                        'invalidType' => 'Value must be a string; received stdClass',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     * @param array<string, mixed> $data
     */
    public function testIsValid(array $data): void
    {
        $filter = new CreateContentNegotiationInputFilter();
        $filter->setData($data);
        self::assertTrue($filter->isValid(), var_export($filter->getMessages(), true));
    }

    /**
     * @dataProvider dataProviderIsInvalid
     * @param array<string, mixed> $data
     * @param array<string, mixed> $messages
     */
    public function testIsInvalid(array $data, array $messages): void
    {
        $filter = new CreateContentNegotiationInputFilter();
        $filter->setData($data);
        self::assertFalse($filter->isValid());
        self::assertEquals($messages, $filter->getMessages());
    }
}
