<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

use Laminas\InputFilter\InputFilter;

use function in_array;

class BaseInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name'          => 'name',
            'error_message' => 'Please provide a name for HTTP authentication',
            'filters'       => [
                ['name' => 'StringToLower'],
            ],
        ]);
        $this->add([
            'name'          => 'type',
            'error_message' => 'Please provide the HTTP authentication type',
            'filters'       => [
                ['name' => 'StringToLower'],
            ],
            'validators'    => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => function ($value) {
                            return in_array($value, ['basic', 'digest', 'oauth2']);
                        },
                    ],
                ],
            ],
        ]);
    }
}
