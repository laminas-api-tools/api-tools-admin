<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

class ContentNegotiationInputFilter extends InputFilter
{
    public function __construct()
    {
        $input = new Input('selectors');
        $chain = $input->getValidatorChain();
        $chain->attach(new Validator\ContentNegotiationSelectorsValidator());
        $this->add($input);
    }
}
