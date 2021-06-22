<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

use function sprintf;

class RestServiceResourceFactory
{
    /**
     * @return RestServiceResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (
            ! $container->has(RestServiceModelFactory::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\RestServiceModelFactory::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency and cannot be created',
                RestServiceResource::class,
                RestServiceModelFactory::class
            ));
        }
        if (
            ! $container->has(InputFilterModel::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\InputFilterModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency and cannot be created',
                RestServiceResource::class,
                InputFilterModel::class
            ));
        }

        return new RestServiceResource(
            $container->has(RestServiceModelFactory::class)
                ? $container->get(RestServiceModelFactory::class)
                : $container->get(\ZF\Apigility\Admin\Model\RestServiceModelFactory::class),
            $container->has(InputFilterModel::class)
                ? $container->get(InputFilterModel::class)
                : $container->get(\ZF\Apigility\Admin\Model\InputFilterModel::class),
            $container->get(DocumentationModel::class)
        );
    }
}
