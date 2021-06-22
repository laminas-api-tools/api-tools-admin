<?php

declare(strict_types=1);

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
     * @param string $requestedName
     * @param null|array $options
     * @return InputFilterInputFilter
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
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
