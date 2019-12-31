<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\RpcService;

class PatchInputFilter extends PostInputFilter
{
    public function init()
    {
        parent::init();

        $this->add(array(
            'name' => 'controller_class',
            'required' => true,
            'error_message' => 'The Controller Class must be a valid, fully qualified, PHP class name',
        ));

        $this->add(array(
            'name' => 'accept_whitelist',
            'validators' => array(
                array('name' => 'Laminas\ApiTools\Admin\InputFilter\Validator\MediaTypeArrayValidator')
            ),
            'error_message' => 'The Accept Whitelist must be an array of valid media type expressions',
        ));
        $this->add(array(
            'name' => 'content_type_whitelist',
            'validators' => array(
                array('name' => 'Laminas\ApiTools\Admin\InputFilter\Validator\MediaTypeArrayValidator')
            ),
            'error_message' => 'The Content-Type Whitelist must be an array of valid media type expressions',
        ));
        $this->add(array(
            'name' => 'selector',
            'required' => false,
            'allow_empty' => true,
            'error_message' => 'The Content Negotiation Selector must be a valid,'
                . ' defined api-tools-content-negotiation selector name',
        ));

        $this->add(array(
            'name' => 'http_methods',
            'validators' => array(
                array('name' => 'Laminas\ApiTools\Admin\InputFilter\Validator\HttpMethodArrayValidator')
            ),
            'error_message' => 'The HTTP Methods must be an array of valid HTTP method names',
        ));
    }
}
