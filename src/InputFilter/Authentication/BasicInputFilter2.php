<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

use function file_exists;

class BasicInputFilter2 extends BaseInputFilter
{
    public function init()
    {
        parent::init();

        $this->add([
            'name'          => 'realm',
            'error_message' => 'Please provide a realm for HTTP basic authentication',
        ]);
        $this->add([
            'name'          => 'htpasswd',
            'validators'    => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => function ($value) {
                            return file_exists($value);
                        },
                    ],
                ],
            ],
            'error_message' => 'Path provided for htpasswd file must exist',
        ]);
    }
}
