<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Digits;

class DigestInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => 'accept_schemes',
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => ['callback' => function ($value) {
                        if (! is_array($value)) {
                            return false;
                        }
                        $allowed = ['digest', 'basic'];
                        foreach ($value as $v) {
                            if (! in_array($v, $allowed)) {
                                return false;
                            }
                        }
                        return true;
                    }],
                ],
            ],
            'error_message' => 'Accept Schemes must be an array containing one or more'
                . ' of the values "basic" or "digest"',
        ]);
        $this->add([
            'name' => 'realm',
            'error_message' => 'Please provide a realm for HTTP basic authentication',
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
        $this->add([
            'name' => 'nonce_timeout',
            'validators' => [
                ['name' => Digits::class],
            ],
            'error_message' => 'Nonce Timeout must be an integer',
        ]);
        $this->add([
            'name' => 'digest_domains',
            'error_message' => 'Digest Domains must be provided as a string',
        ]);
    }
}
