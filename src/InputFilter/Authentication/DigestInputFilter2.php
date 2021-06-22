<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

use function file_exists;
use function is_numeric;

class DigestInputFilter2 extends BaseInputFilter
{
    public function init()
    {
        parent::init();

        $this->add([
            'name'          => 'realm',
            'error_message' => 'Please provide a realm for HTTP digest authentication',
        ]);
        $this->add([
            'name'          => 'digest_domains',
            'error_message' => 'Please provide a digest domains for HTTP digest authentication',
        ]);
        $this->add([
            'name'          => 'nonce_timeout',
            'validators'    => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => function ($value) {
                            return is_numeric($value);
                        },
                    ],
                ],
            ],
            'error_message' => 'Please provide a valid nonce timeout for HTTP digest authentication',
        ]);
        $this->add([
            'name'          => 'htdigest',
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
            'error_message' => 'Path provided for htdigest file must exist',
        ]);
    }
}
