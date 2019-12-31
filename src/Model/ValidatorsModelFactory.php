<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ValidatorsModelFactory implements FactoryInterface
{
    /**
     * Create and return a ValidatorsModel instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ValidatorsModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has('ValidatorManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the ValidatorManager service be present; service not found',
                get_class($this)
            ));
        }

        if (! $container->has(ValidatorMetadataModel::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\ValidatorMetadataModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                get_class($this),
                ValidatorMetadataModel::class
            ));
        }

        return new ValidatorsModel(
            $container->get('ValidatorManager'),
            $container->has(ValidatorMetadataModel::class) ? $container->get(ValidatorMetadataModel::class) : $container->get(\ZF\Apigility\Admin\Model\ValidatorMetadataModel::class)
        );
    }

    /**
     * Create and return a ValidatorsModel instance v2.
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ValidatorsModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ValidatorsModel::class);
    }
}
