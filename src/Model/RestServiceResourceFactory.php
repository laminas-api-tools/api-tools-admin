<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class RestServiceResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return RestServiceResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(RestServiceModelFactory::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\RestServiceModelFactory::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency and cannot be created',
                RestServiceResource::class,
                RestServiceModelFactory::class
            ));
        }
        if (! $container->has(InputFilterModel::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\InputFilterModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency and cannot be created',
                RestServiceResource::class,
                InputFilterModel::class
            ));
        }

        return new RestServiceResource(
            $container->has(RestServiceModelFactory::class) ? $container->get(RestServiceModelFactory::class) : $container->get(\ZF\Apigility\Admin\Model\RestServiceModelFactory::class),
            $container->has(InputFilterModel::class) ? $container->get(InputFilterModel::class) : $container->get(\ZF\Apigility\Admin\Model\InputFilterModel::class),
            $container->get(DocumentationModel::class)
        );
    }
}
