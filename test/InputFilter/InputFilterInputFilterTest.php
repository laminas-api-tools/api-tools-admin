<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\InputFilterInputFilter;
use Laminas\Filter\Boolean;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

class InputFilterInputFilterTest extends TestCase
{
    /** @var InputFilterInputFilter */
    private $inputFilterInputFilter;

    public function setup(): void
    {
        $this->inputFilterInputFilter = new InputFilterInputFilter(new Factory());
    }

    /** @psalm-return array<array-key, array> */
    public function dataProviderIsValid(): array
    {
        return [
            [
                [
                    [
                        'name'              => 'myfilter',
                        'required'          => true,
                        'filters'           => [
                            [
                                'name'    => Boolean::class,
                                'options' => ['casting' => false],
                            ],
                        ],
                        'validators'        => [],
                        'allow_empty'       => true,
                        'continue_if_empty' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @psalm-return array<array-key, array{
     *     0: array,
     *     1: array<string, string>
     * }>
     */
    public function dataProviderIsInvalid(): array
    {
        return [
            [
                ['foobar' => 'baz'],
                [
                    'inputFilter' => 'Laminas\InputFilter\Factory::createInput expects'
                    . ' an array or Traversable; received "string"',
                ],
            ],
            [
                [
                    [
                        'name'              => 'myfilter',
                        'required'          => true,
                        'filters'           => [
                            [
                                'name'    => 'Laminas\Filter\Bool',
                                'options' => ['casting' => false],
                            ],
                        ],
                        'validators'        => [],
                        'allow_empty'       => true,
                        'continue_if_empty' => false,
                    ],
                ],
                [
                    'inputFilter' => 'Laminas\Filter\Bool',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderIsValid
     * @param array<string, mixed>[] $data
     */
    public function testIsValid(array $data): void
    {
        $this->inputFilterInputFilter->setData($data);
        self::assertTrue($this->inputFilterInputFilter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     * @param array<string, mixed>|array<string, mixed>[] $data
     * @param array<string, mixed> $messages
     */
    public function testIsInvalid(array $data, array $messages): void
    {
        $this->inputFilterInputFilter->setData($data);
        self::assertFalse($this->inputFilterInputFilter->isValid());
        $validationMessages = $this->inputFilterInputFilter->getMessages();
        self::assertArrayHasKey('inputFilter', $validationMessages);
        self::assertStringContainsString($messages['inputFilter'], $validationMessages['inputFilter']);
    }
}
