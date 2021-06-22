<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter\RpcService;

use Laminas\ApiTools\Admin\InputFilter\Validator\ServiceNameValidator;
use Laminas\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    public function init()
    {
        $this->add([
            'name'          => 'service_name',
            'validators'    => [
                [
                    'name' => ServiceNameValidator::class,
                ],
            ],
            'error_message' => 'Service Name is required, and must be a valid PHP class name',
        ]);
        $this->add([
            'name'          => 'route_match',
            'error_message' => 'Route Match is required, and must be a valid URI path',
        ]);
    }
}
