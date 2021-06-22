<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\ConfigWriter;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

use function sprintf;

class DbAdapterModelFactory
{
    /**
     * @return DbAdapterModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because config service is not present',
                DbAdapterModel::class
            ));
        }

        $config = $container->get('config');
        $writer = $container->get(ConfigWriter::class);

        return new DbAdapterModel(
            new ConfigResource($config, 'config/autoload/global.php', $writer),
            new ConfigResource($config, 'config/autoload/local.php', $writer)
        );
    }
}
