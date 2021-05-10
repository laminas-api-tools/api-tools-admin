<?php

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
