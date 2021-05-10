<?php

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class DbAdapterResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return DbAdapterResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(DbAdapterModel::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\DbAdapterModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                DbAdapterResource::class,
                DbAdapterModel::class
            ));
        }
        return new DbAdapterResource(
            $container->has(DbAdapterModel::class)
                ? $container->get(DbAdapterModel::class)
                : $container->get(\ZF\Apigility\Admin\Model\DbAdapterModel::class)
        );
    }
}
