<?php

namespace LaminasTest\ApiTools\Admin\InputFilter\Validator;

use Laminas\ApiTools\Admin\InputFilter\Validator\ModuleNameValidator;
use PHPUnit\Framework\TestCase;

class ModuleNameTest extends TestCase
{
    public function validModuleNames()
    {
        return [
            'string' => ['test'],
            'string-with-underscores' => ['test_test'],
            'string-with-digits' => ['test0'],
        ];
    }

    public function invalidModuleNames()
    {
        return [
            'eval' => ['eval'],
            'Eval' => ['Eval'],
            'digit-leading' => ['0test'],
        ];
    }

    /**
     * @dataProvider validModuleNames
     */
    public function testValidModuleName($name)
    {
        $validator = new ModuleNameValidator();
        $this->assertTrue($validator->isValid($name));
    }

    /**
     * @dataProvider invalidModuleNames
     */
    public function testInvalidModuleName($name)
    {
        $validator = new ModuleNameValidator();
        $this->assertFalse($validator->isValid($name));
    }
}
