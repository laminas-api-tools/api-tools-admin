<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\ContentNegotiationInputFilter;
use Laminas\View\Model\ViewModel;
use PHPUnit\Framework\TestCase;

use function var_export;

class ContentNegotiationInputFilterTest extends TestCase
{
    /** @psalm-return array<string, array{0: array<string, array<string, string[]>>}> */
    public function dataProviderIsValid(): array
    {
        return [
            'valid' => [
                [
                    'selectors'
                    => [
                        ViewModel::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, array<string, string|string[]>>,
     *     1: array<string, array<string, string>>
     * }>
     */
    public function dataProviderIsInvalid(): array
    {
        return [
            'class-does-not-exist'    => [
                [
                    'selectors' => [
                        'Laminas\View\Model\ViewMode' => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'selectors' => [
                        'classNotFound' => 'Class name (Laminas\View\Model\ViewMode) does not exist',
                    ],
                ],
            ],
            'class-is-not-view-model' => [
                [
                    'selectors' => [
                        self::class => ['text/html', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'selectors' => [
                        'invalidViewModel' => 'Class name (' . self::class . ') is invalid;'
                    . ' must be a valid Laminas\View\Model\ModelInterface instance',
                    ],
                ],
            ],
            'media-types-not-array'   => [
                [
                    'selectors' => [
                        ViewModel::class => 'foo',
                    ],
                ],
                [
                    'selectors' => [
                        'invalidMediaTypes' => 'Values for the media-types must be provided as an indexed array',
                    ],
                ],
            ],
            'invalid-media-type'      => [
                [
                    'selectors' => [
                        ViewModel::class => ['texthtml', 'application/xhtml+xml'],
                    ],
                ],
                [
                    'selectors' => [
                        'invalidMediaType' => 'Invalid media type (texthtml) provided',
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
        $filter = new ContentNegotiationInputFilter();
        $filter->setData($data);
        self::assertTrue($filter->isValid(), var_export($filter->getMessages(), true));
    }

    /**
     * @dataProvider dataProviderIsInvalid
     * @param array<string, mixed> $data
     */
    public function testIsInvalid(array $data, array $messages): void
    {
        $filter = new ContentNegotiationInputFilter();
        $filter->setData($data);
        $filter->get('selectors');
        self::assertFalse($filter->isValid());
        self::assertEquals($messages, $filter->getMessages());
    }
}
