<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\InputFilter\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\InputFilter\InputFilterInputFilter;
use Laminas\InputFilter\Factory as InputFilterFactory;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class InputFilterInputFilterFactory implements FactoryInterface
{
    /**
     * Create and return an InputFilterInputFilter instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return InputFilterInputFilter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $factory = new InputFilterFactory();
        $factory->setInputFilterManager($container->get('InputFilterManager'));
        $factory->getDefaultFilterChain()->setPluginManager($container->get('FilterManager'));
        $factory->getDefaultValidatorChain()->setPluginManager($container->get('ValidatorManager'));

        return new InputFilterInputFilter($factory);
    }

    /**
     * Create and return an InputFilterInputFilter instance.
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return InputFilterInputFilter
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, InputFilterInputFilter::class);
    }
}
