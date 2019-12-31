<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\RpcService;

use Laminas\InputFilter\InputFilter;

class PostInputFilter extends InputFilter
{
    public function init()
    {
        $this->add(array(
            'name' => 'service_name',
            'validators' => array(
                array(
                    'name' => 'Laminas\ApiTools\Admin\InputFilter\Validator\ServiceNameValidator',
                ),
            ),
            'error_message' => 'Service Name is required, and must be a valid PHP class name',
        ));
        $this->add(array(
            'name' => 'route_match',
            'error_message' => 'Route Match is required, and must be a valid URI path',
        ));
    }
}
