<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

use function sprintf;

class ContentNegotiationResourceFactory
{
    /**
     * @return ContentNegotiationResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (
            ! $container->has(ContentNegotiationModel::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\ContentNegotiationModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                ContentNegotiationResource::class,
                ContentNegotiationModel::class
            ));
        }

        return new ContentNegotiationResource(
            $container->has(ContentNegotiationModel::class)
                ? $container->get(ContentNegotiationModel::class)
                : $container->get(\ZF\Apigility\Admin\Model\ContentNegotiationModel::class)
        );
    }
}
