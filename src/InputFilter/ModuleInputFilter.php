<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\Validator\ModuleNameValidator;
use Laminas\InputFilter\InputFilter;

class ModuleInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => 'name',
            'validators' => [
                [
                    'name' => ModuleNameValidator::class,
                ],
            ],
            'error_message' => 'The API name must be a valid PHP namespace',
        ]);
    }
}
