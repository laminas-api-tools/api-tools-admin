<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ValidatorMetadataModelFactory implements FactoryInterface
{
    /**
     * Create and return a ValidatorMetadataModel instance.
     *
     * @param string $requestedName
     * @param null|array $options
     * @return ValidatorMetadataModel
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
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
     * @return ValidatorMetadataModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ValidatorMetadataModel::class);
    }
}
