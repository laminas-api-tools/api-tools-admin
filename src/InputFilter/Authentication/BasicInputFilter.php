<?php

namespace Laminas\ApiTools\Admin\InputFilter\Authentication;

use Laminas\InputFilter\InputFilter;

class BasicInputFilter extends InputFilter
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
            'name' => 'htpasswd',
            'validators' => [
                [
                    'name' => 'Callback',
                    'options' => ['callback' => function ($value) {
                        return file_exists($value);
                    }],
                ],
            ],
            'error_message' => 'Path provided for htpasswd file must exist',
        ]);
    }
}
