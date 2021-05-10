<?php

namespace Laminas\ApiTools\Admin\InputFilter\RpcService;

use Laminas\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name' => 'service_name',
            'validators' => [
                [
                    'name' => \Laminas\ApiTools\Admin\InputFilter\Validator\ServiceNameValidator::class,
                ],
            ],
            'error_message' => 'Service Name is required, and must be a valid PHP class name',
        ]);
        $this->add([
            'name' => 'route_match',
            'error_message' => 'Route Match is required, and must be a valid URI path',
        ]);
    }
}
