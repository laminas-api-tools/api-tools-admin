<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

class OAuth2MongoInputFilter2 extends BaseInputFilter
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'oauth2_type',
            'filters' => [
                ['name' => 'StringToLower'],
            ],
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => ['callback' => function ($value) {
                        return ($value === 'mongo');
                    }],
                ],
            ],
            'error_message' => 'Please provide a valid DSN type adapter (pdo, mongo)',
        ]);
        $this->add([
            'name' => 'oauth2_dsn',
            'error_message' => 'Please provide a valid DSN for OAuth2 database',
            'required' => false,
        ]);
        $this->add([
            'name' => 'oauth2_database',
            'error_message' => 'Please provide a valid database name for OAuth2 Mongo adapter',
        ]);
        $this->add([
            'name' => 'oauth2_route',
            'validators' => [
                [
                    'name' => 'Uri',
                    'options' => [
                        'allowRelative' => true,
                    ],
                ],
            ],
            'error_message' => 'Please provide a valid URL route for OAuth2 Mongo adapter',
        ]);
        $this->add([
            'name' => 'oauth2_locator_name',
            'error_message' => 'Please provide a valid locator name for OAuth2 Mongo adapter',
            'required' => false,
        ]);
        $this->add([
            'name' => 'oauth2_options',
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => ['callback' => function ($value) {
                        return is_array($value);
                    }],
                ],
            ],
            'error_message' => 'Please provide a valid options for OAuth2 Mongo adapter',
            'required' => false,
        ]);
    }
}
