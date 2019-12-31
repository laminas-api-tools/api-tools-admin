<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

use Laminas\InputFilter\InputFilter;

class BaseInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => 'name',
            'error_message' => 'Please provide a name for HTTP authentication',
            'filters' => [
                ['name' => 'StringToLower'],
            ],
        ]);
        $this->add([
            'name' => 'type',
            'error_message' => 'Please provide the HTTP authentication type',
            'filters' => [
                ['name' => 'StringToLower'],
            ],
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => ['callback' => function ($value) {
                        return in_array($value, ['basic', 'digest', 'oauth2']);
                    }],
                ],
            ],
        ]);
    }
}
