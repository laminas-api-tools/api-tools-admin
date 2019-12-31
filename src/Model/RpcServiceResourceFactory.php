<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class RpcServiceResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return RpcServiceResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(RpcServiceModelFactory::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\RpcServiceModelFactory::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency',
                RpcServiceResource::class,
                RpcServiceModelFactory::class
            ));
        }
        if (! $container->has(InputFilterModel::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\InputFilterModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency',
                RpcServiceResource::class,
                InputFilterModel::class
            ));
        }
        if (! $container->has('ControllerManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its ControllerManager dependency',
                RpcServiceResource::class
            ));
        }

        return new RpcServiceResource(
            $container->has(RpcServiceModelFactory::class) ? $container->get(RpcServiceModelFactory::class) : $container->get(\ZF\Apigility\Admin\Model\RpcServiceModelFactory::class),
            $container->has(InputFilterModel::class) ? $container->get(InputFilterModel::class) : $container->get(\ZF\Apigility\Admin\Model\InputFilterModel::class),
            $container->get('ControllerManager'),
            $container->get(DocumentationModel::class)
        );
    }
}
