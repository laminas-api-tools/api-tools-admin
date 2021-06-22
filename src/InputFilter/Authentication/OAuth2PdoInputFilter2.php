<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

use function is_array;

class OAuth2PdoInputFilter2 extends BaseInputFilter
{
    public function init()
    {
        parent::init();

        $this->add([
            'name'          => 'oauth2_type',
            'filters'       => [
                ['name' => 'StringToLower'],
            ],
            'validators'    => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => function ($value) {
                            return $value === 'pdo';
                        },
                    ],
                ],
            ],
            'error_message' => 'Please provide a valid DSN type adapter (pdo, mongo)',
        ]);
        $this->add([
            'name'          => 'oauth2_dsn',
            'error_message' => 'Please provide a valid DSN for OAuth2 PDO adapter',
        ]);
        $this->add([
            'name'          => 'oauth2_username',
            'error_message' => 'Please provide a username for OAuth2 PDO database',
            'required'      => false,
        ]);
        $this->add([
            'name'          => 'oauth2_password',
            'error_message' => 'Please provide a password DSN for OAuth2 PDO database',
            'required'      => false,
        ]);
        $this->add([
            'name'          => 'oauth2_route',
            'validators'    => [
                [
                    'name'    => 'Uri',
                    'options' => [
                        'allowRelative' => true,
                    ],
                ],
            ],
            'error_message' => 'Please provide a valid URL route for OAuth2 PDO adapter',
        ]);
        $this->add([
            'name'          => 'oauth2_options',
            'validators'    => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => function ($value) {
                            return is_array($value);
                        },
                    ],
                ],
            ],
            'error_message' => 'Please provide a valid options for OAuth2 PDO adapter',
            'required'      => false,
        ]);
    }
}
