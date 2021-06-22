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
     * @psalm-return array<string, array {
     *     0: array<string, array<string, mixed>>,
     *     1: array<string, array<string, string>>
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
     */
    public function testIsValid(array $data)
    {
        $filter = new CreateContentNegotiationInputFilter();
        $filter->setData($data);
        $this->assertTrue($filter->isValid(), var_export($filter->getMessages(), true));
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid(array $data, array $messages)
    {
        $filter = new CreateContentNegotiationInputFilter();
        $filter->setData($data);
        $this->assertFalse($filter->isValid());
        $this->assertEquals($messages, $filter->getMessages());
    }
}
