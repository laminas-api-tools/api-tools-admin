<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Factory;

use Laminas\ApiTools\Admin\InputFilter\InputFilterInputFilter;
use Laminas\InputFilter\Factory as InputFilterFactory;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class InputFilterInputFilterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $inputFilters
     * @return InputFilterInputFilter
     */
    public function createService(ServiceLocatorInterface $inputFilters)
    {
        $services = $inputFilters->getServiceLocator();
        $factory  = new InputFilterFactory();
        $factory->setInputFilterManager($inputFilters);
        $factory->getDefaultFilterChain()->setPluginManager($services->get('FilterManager'));
        $factory->getDefaultValidatorChain()->setPluginManager($services->get('ValidatorManager'));

        return new InputFilterInputFilter($factory);
    }
}
