<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\RpcService;

use Laminas\ApiTools\Admin\InputFilter\Validator\HttpMethodArrayValidator;
use Laminas\ApiTools\Admin\InputFilter\Validator\MediaTypeArrayValidator;

class PatchInputFilter extends PostInputFilter
{
    public function init()
    {
        parent::init();

        $this->add([
            'name' => 'controller_class',
            'required' => true,
            'error_message' => 'The Controller Class must be a valid, fully qualified, PHP class name',
        ]);

        $this->add([
            'name' => 'accept_whitelist',
            'validators' => [
                ['name' => MediaTypeArrayValidator::class],
            ],
            'error_message' => 'The Accept Whitelist must be an array of valid media type expressions',
        ]);
        $this->add([
            'name' => 'content_type_whitelist',
            'validators' => [
                ['name' => MediaTypeArrayValidator::class],
            ],
            'error_message' => 'The Content-Type Whitelist must be an array of valid media type expressions',
        ]);
        $this->add([
            'name' => 'selector',
            'required' => false,
            'allow_empty' => true,
            'error_message' => 'The Content Negotiation Selector must be a valid,'
                . ' defined api-tools-content-negotiation selector name',
        ]);

        $this->add([
            'name' => 'http_methods',
            'validators' => [
                ['name' => HttpMethodArrayValidator::class],
            ],
            'error_message' => 'The HTTP Methods must be an array of valid HTTP method names',
        ]);
    }
}
