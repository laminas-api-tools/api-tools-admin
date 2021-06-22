<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\InputFilter;

use Laminas\InputFilter\Input;

class CreateContentNegotiationInputFilter extends ContentNegotiationInputFilter
{
    public function __construct()
    {
        parent::__construct();

        $this->get('selectors')->setRequired(false);

        $input = new Input('content_name');
        $input->setRequired(true);
        $chain = $input->getValidatorChain();
        $chain->attach(new Validator\IsStringValidator());
        $this->add($input);
    }
}
