<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class ModuleModelFactory
{
    /**
     * @param ContainerInterface $container
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

        return new ModuleModel(
            $container->get('ModuleManager'),
            $this->getNamedConfigArray('api-tools-rest', $config),
            $this->getNamedConfigArray('api-tools-rpc', $config)
        );
    }

    /**
     * @param ContainerInterface $container
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
        return (isset($config[$name]) && is_array($config[$name]))
            ? $config[$name]
            : [];
    }
}
