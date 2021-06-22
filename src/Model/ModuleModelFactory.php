<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

use function is_array;
use function sprintf;

class ModuleModelFactory
{
    /**
     * @return ModuleModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has('ModuleManager')) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because ModuleManager service is not present',
                ModuleModel::class
            ));
        }

        $config = $this->getConfig($container);

        $model = new ModuleModel(
            $container->get('ModuleManager'),
            $this->getNamedConfigArray('api-tools-rest', $config),
            $this->getNamedConfigArray('api-tools-rpc', $config)
        );

        $model->setUseShortArrayNotation($this->useShortArrayNotation($config));

        return $model;
    }

    /**
     * @return array
     */
    private function getConfig(ContainerInterface $container)
    {
        return $container->has('config') ? $container->get('config') : [];
    }

    /**
     * @param string $name Config key to retrieve
     * @param array $config
     * @return array
     */
    private function getNamedConfigArray($name, array $config)
    {
        return isset($config[$name]) && is_array($config[$name])
            ? $config[$name]
            : [];
    }

    /**
     * Determine whether or not to enable generation of short array notation
     *
     * @param array $config
     * @return bool
     */
    private function useShortArrayNotation(array $config)
    {
        $config = $this->getNamedConfigArray('api-tools-configuration', $config);
        if (
            ! isset($config['enable_short_array'])
            || false === $config['enable_short_array']
        ) {
            return false;
        }

        return true;
    }
}
