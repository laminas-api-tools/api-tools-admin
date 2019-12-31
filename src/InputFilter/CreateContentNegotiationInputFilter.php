<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

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
