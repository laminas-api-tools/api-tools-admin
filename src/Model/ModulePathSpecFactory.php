<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ModuleUtils;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class ModulePathSpecFactory
{
    /**
     * @param ContainerInterface $container
     * @return ModulePathSpec
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ModuleUtils::class)
            && ! $container->has(\ZF\Configuration\ModuleUtils::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                ModulePathSpec::class,
                ModuleUtils::class
            ));
        }

        $config = $this->getConfig($container);

        return new ModulePathSpec(
            $container->has(ModuleUtils::class) ? $container->get(ModuleUtils::class) : $container->get(\ZF\Configuration\ModuleUtils::class),
            $this->getPathSpecFromConfig($config),
            $this->getPathFromConfig($config)
        );
    }

    /**
     * Retrieve the api-tools-admin configuration array, if present.
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function getConfig(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return [];
        }

        $config = $container->get('config');

        if (! isset($config['api-tools-admin'])
            || ! is_array($config['api-tools-admin'])
        ) {
            return [];
        }

        return $config['api-tools-admin'];
    }

    /**
     * @param array $config
     * @return string Value of 'path_spec'; defaults to psr-0
     */
    private function getPathSpecFromConfig(array $config)
    {
        return isset($config['path_spec']) ? $config['path_spec'] : 'psr-0';
    }

    /**
     * @param array $config
     * @return string '.' if no module_path found in configuration, otherwise
     *     value of module_path.
     * @throws ServiceNotCreatedException if configured module_path is not a
     *     valid directory.
     */
    private function getPathFromConfig(array $config)
    {
        $default = '.';

        if (! isset($config['module_path'])) {
            return $default;
        }

        if (! is_dir($config['module_path'])) {
            throw new ServiceNotCreatedException(sprintf(
                'Invalid module path "%s"; does not exist',
                $config['module_path']
            ));
        }

        return $config['module_path'];
    }
}
