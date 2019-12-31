<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

class DoctrineAdapterResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return DoctrineAdapterResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(DoctrineAdapterModel::class)
            && ! $container->has(\ZF\Apigility\Admin\Model\DoctrineAdapterModel::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                DoctrineAdapterResource::class,
                DoctrineAdapterModel::class
            ));
        }

        $model = $container->has(DoctrineAdapterModel::class) ? $container->get(DoctrineAdapterModel::class) : $container->get(\ZF\Apigility\Admin\Model\DoctrineAdapterModel::class);

        $modules = $container->get('ModuleManager');
        $loadedModules = $modules->getLoadedModules(false);

        $resource = new DoctrineAdapterResource($model, $loadedModules);

        // @todo Remove once setServiceLocator and getServiceLocator are removed
        //     from the DoctrineAdapterResource class.
        $resource->setServiceLocator($container);

        return $resource;
    }
}
