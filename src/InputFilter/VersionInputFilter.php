<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\Validator\ModuleNameValidator;
use Laminas\InputFilter\InputFilter;

class VersionInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => 'module',
            'validators' => [
                ['name' => ModuleNameValidator::class],
            ],
            'error_message' => 'Please provide a valid API module name',
        ]);
        $this->add([
            'name' => 'version',
            'validators' => [
                [
                    'name' => 'Regex',
                    'options' => [
                        'pattern' => '/^[a-z0-9_]+$/',
                    ],
                ],
            ],
            'error_message' => 'Please provide a valid version string; may consist of a-Z, 0-9, and "_"',
        ]);
    }
}
