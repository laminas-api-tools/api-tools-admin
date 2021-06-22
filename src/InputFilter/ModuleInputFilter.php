<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\ApiTools\Admin\InputFilter\Validator\ModuleNameValidator;
use Laminas\InputFilter\InputFilter;

class ModuleInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name'          => 'name',
            'validators'    => [
                [
                    'name' => ModuleNameValidator::class,
                ],
            ],
            'error_message' => 'The API name must be a valid PHP namespace',
        ]);
    }
}
