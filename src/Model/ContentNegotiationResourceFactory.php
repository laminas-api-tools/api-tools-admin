<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class ContentNegotiationResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return ContentNegotiationResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ContentNegotiationModel::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\ContentNegotiationModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                ContentNegotiationResource::class,
                ContentNegotiationModel::class
            ));
        }

        return new ContentNegotiationResource(
            $container->has(ContentNegotiationModel::class) ? $container->get(ContentNegotiationModel::class) : $container->get(\ZF\Apigility\Admin\Model\ContentNegotiationModel::class)
        );
    }
}
