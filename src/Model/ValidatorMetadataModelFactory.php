<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ValidatorMetadataModelFactory implements FactoryInterface
{
    /**
     * Create and return a ValidatorMetadataModel instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ValidatorMetadataModel
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $metadata = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            if (isset($config['validator_metadata'])) {
                $metadata = $config['validator_metadata'];
            }
        }

        return new ValidatorMetadataModel($metadata);
    }

    /**
     * Create and return a ValidatorMetadataModel instance.
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ValidatorMetadataModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ValidatorMetadataModel::class);
    }
}
