<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\ApiTools\Admin\InputFilter\Validator;

use Laminas\ApiTools\Admin\InputFilter\Validator\ModuleNameValidator;
use PHPUnit_Framework_TestCase as TestCase;

class ModuleNameTest extends TestCase
{
    public function validModuleNames()
    {
        return array(
            'string' => array('test'),
            'string-with-underscores' => array('test_test'),
            'string-with-digits' => array('test0'),
        );
    }

    public function invalidModuleNames()
    {
        return array(
            'eval' => array('eval'),
            'Eval' => array('Eval'),
            'digit-leading' => array('0test'),
        );
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
