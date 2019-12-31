<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

class DigestInputFilter2 extends BaseInputFilter
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'realm',
            'error_message' => 'Please provide a realm for HTTP digest authentication',
        ]);
        $this->add([
            'name' => 'digest_domains',
            'error_message' => 'Please provide a digest domains for HTTP digest authentication',
        ]);
        $this->add([
            'name' => 'nonce_timeout',
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => ['callback' => function ($value) {
                        return is_numeric($value);
                    }],
                ],
            ],
            'error_message' => 'Please provide a valid nonce timeout for HTTP digest authentication',
        ]);
        $this->add([
            'name' => 'htdigest',
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => ['callback' => function ($value) {
                        return file_exists($value);
                    }],
                ],
            ],
            'error_message' => 'Path provided for htdigest file must exist',
        ]);
    }
}
