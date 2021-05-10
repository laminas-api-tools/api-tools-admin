<?php

namespace Laminas\ApiTools\Admin\InputFilter\RestService;

use Laminas\ApiTools\Admin\InputFilter\Validator\HttpMethodArrayValidator;
use Laminas\Validator\Callback as CallbackValidator;

class PatchInputFilter extends PostInputFilter
{
    protected $isUpdate = true;

    public function init()
    {
        parent::init();

        // classes
        $this->add([
            'name' => 'resource_class',
            'required' => true,
            'allow_empty' => true,
            'error_message' => 'The Resource Class must be a valid class name',
        ]);
        $this->add([
            'name' => 'collection_class',
            'required' => true,
            'allow_empty' => false,
            'error_message' => 'The Collection Class must be a valid class name',
        ]);
        $this->add([
            'name' => 'entity_class',
            'required' => true,
            'allow_empty' => false,
            'error_message' => 'The Entity Class must be a valid class name',
        ]);

        $this->add([
            'name' => 'route_match',
            'required' => true,
            'allow_empty' => false,
            'error_message' => 'The Route must be a non-empty URI path',
        ]);

        $this->add([
            'name' => 'accept_whitelist',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'error_message' => 'The Accept Whitelist must be an array of valid mediatype expressions',
        ]);
        $this->add([
            'name' => 'content_type_whitelist',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'error_message' => 'The Content-Type Whitelist must be an array of valid mediatype expressions',
        ]);
        $this->add([
            'name' => 'selector',
            'required' => false,
            'allow_empty' => true,
            'error_message' => 'The Content Negotiation Selector must be a valid,'
                . ' defined api-tools-content-negotiation selector',
        ]);

        $this->add([
            'name' => 'page_size',
            'required' => false,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'validators' => [
                new CallbackValidator(function ($value) {
                    if (intval($value) != $value) {
                        return false;
                    }

                    $value = intval($value);
                    if ($value === -1
                        || $value > 0
                    ) {
                        return true;
                    }

                    return false;
                }),
            ],
            'error_message' => 'The Page Size must be either a positive integer'
                . ' or the value "-1" (disabling pagination)',
        ]);
        $this->add([
            'name' => 'collection_http_methods',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'validators' => [
                ['name' => HttpMethodArrayValidator::class],
            ],
            'error_message' => 'The Collection HTTP Methods must be an array of valid HTTP methods',
        ]);
        $this->add([
            'name' => 'entity_http_methods',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'validators' => [
                ['name' => HttpMethodArrayValidator::class],
            ],
            'error_message' => 'The Entity HTTP Methods must be an array of valid HTTP methods',
        ]);
        $this->add([
            'name' => 'route_identifier_name',
            'required' => true,
            'allow_empty' => false,
            'error_message' => 'The Route Identifier Name must be a non-empty string',
        ]);
        $this->add([
            'name' => 'entity_identifier_name',
            'required' => true,
            'allow_empty' => false,
            'error_message' => 'The Entity Identifier Name must be a non-empty string',
        ]);
        $this->add([
            'name' => 'hydrator_name',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'error_message' => 'The Hydrator Name must be either empty, or a valid Hydrator service name',
        ]);
        $this->add([
            'name' => 'collection_name',
            'required' => true,
            'allow_empty' => false,
            'error_message' => 'The Collection Name must be a non-empty string',
        ]);
        $this->add([
            'name' => 'page_size_param',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'error_message' => 'The Page Size Parameter must empty or a string',
        ]);
        $this->add([
            'name' => 'collection_query_whitelist',
            'required' => true,
            'allow_empty' => true,
            'continue_if_empty' => true,
            'error_message' => 'The Collection Query Whitelist must either be empty or an array of strings',
        ]);
    }
}
