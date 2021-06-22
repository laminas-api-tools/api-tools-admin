<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DocumentationControllerFactory implements FactoryInterface
{
    /**
     * Create and return DocumentationController instance.
     *
     * @param string $requestedName
     * @param null|array $options
     * @return DocumentationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new DocumentationController($container->get(DocumentationModel::class));
    }

    /**
     * Create and return DocumentationController instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @return DocumentationController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, DocumentationController::class);
    }
}
