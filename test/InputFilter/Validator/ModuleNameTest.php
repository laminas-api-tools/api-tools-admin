<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\InputFilter\Validator;

use Laminas\ApiTools\Admin\InputFilter\Validator\ModuleNameValidator;
use PHPUnit\Framework\TestCase;

class ModuleNameTest extends TestCase
{
    /** @psalm-return array<string, array{0: string}> */
    public function validModuleNames(): array
    {
        return [
            'string'                  => ['test'],
            'string-with-underscores' => ['test_test'],
            'string-with-digits'      => ['test0'],
        ];
    }

    /** @psalm-return array<string, array{0: string}> */
    public function invalidModuleNames(): array
    {
        return [
            'eval'          => ['eval'],
            'Eval'          => ['Eval'],
            'digit-leading' => ['0test'],
        ];
    }

    /**
     * @dataProvider validModuleNames
     */
    public function testValidModuleName(string $name)
    {
        $validator = new ModuleNameValidator();
        $this->assertTrue($validator->isValid($name));
    }

    /**
     * @dataProvider invalidModuleNames
     */
    public function testInvalidModuleName(string $name)
    {
        $validator = new ModuleNameValidator();
        $this->assertFalse($validator->isValid($name));
    }
}
