<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\VersionInputFilter;
use Laminas\InputFilter\Factory;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function sort;

class VersionInputFilterTest extends TestCase
{
    public function getInputFilter(): VersionInputFilter
    {
        $factory = new Factory();
        return $factory->createInputFilter([
            'type' => VersionInputFilter::class,
        ]);
    }

    /** @psalm-return array<string, array{0: array<string, string|int>}> */
    public function dataProviderIsValid(): array
    {
        return [
            'valid'               => [
                [
                    'module'  => 'foo',
                    'version' => 5,
                ],
            ],
            'version-with-alphas' => [
                [
                    'module'  => 'foo',
                    'version' => 'alpha',
                ],
            ],
            'version-with-mixed'  => [
                [
                    'module'  => 'foo',
                    'version' => 'alpha_1',
                ],
            ],
        ];
    }

    /**
     * @psalm-return array<string, array{
     *     0: array<string, string>
     *     1: string[]
     * }>
     */
    public function dataProviderIsInvalid(): array
    {
        return [
            'empty'               => [
                [],
                ['module', 'version'],
            ],
            'missing-module'      => [
                ['version' => 'foo'],
                ['module'],
            ],
            'missing-version'     => [
                ['module' => 'foo'],
                ['version'],
            ],
            'version-with-spaces' => [
                ['module' => 'foo', 'version' => 'foo bar'],
                ['version'],
            ],
            'version-with-dashes' => [
                ['module' => 'foo', 'version' => 'foo-bar'],
                ['version'],
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
