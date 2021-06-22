<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\InputFilterInputFilter;
use Laminas\Filter\Boolean;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

class InputFilterInputFilterTest extends TestCase
{
    public function setup()
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
     */
    public function testIsValid(array $data)
    {
        $this->inputFilterInputFilter->setData($data);
        $this->assertTrue($this->inputFilterInputFilter->isValid());
    }

    /**
     * @dataProvider dataProviderIsInvalid
     */
    public function testIsInvalid(array $data, array $messages)
    {
        $this->inputFilterInputFilter->setData($data);
        $this->assertFalse($this->inputFilterInputFilter->isValid());
        $validationMessages = $this->inputFilterInputFilter->getMessages();
        $this->assertArrayHasKey('inputFilter', $validationMessages);
        $this->assertContains($messages['inputFilter'], $validationMessages['inputFilter']);
    }
}
