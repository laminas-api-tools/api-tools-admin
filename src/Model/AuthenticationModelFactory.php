<?php

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\ConfigWriter;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class AuthenticationModelFactory
{
    /**
     * @param ContainerInterface $container
     * @return AuthenticationModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because config service is not present',
                AuthenticationModel::class
            ));
        }

        $config = $container->get('config');
        $writer = $container->get(ConfigWriter::class);

        return new AuthenticationModel(
            new ConfigResource($config, 'config/autoload/global.php', $writer),
            new ConfigResource($config, 'config/autoload/local.php', $writer),
            $container->get(ModuleModel::class)
        );
    }
}
