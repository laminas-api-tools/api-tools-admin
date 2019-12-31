<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AbstractPluginManagerModelFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $pluginManagerService;

    /**
     * @var string
     */
    protected $pluginManagerModel;

    /**
     * Return one of the plugin manager model instances
     *
     * @param ServiceLocatorInterface $services
     * @return object
     */
    public function createService(ServiceLocatorInterface $services)
    {
        if (null === $this->pluginManagerService
            || null === $this->pluginManagerModel
            || ! class_exists($this->pluginManagerModel)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is an invalid factory; please check the $pluginManagerService and/or $pluginManagerModel values',
                get_class($this)
            ));
        }

        if (! $services->has($this->pluginManagerService)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                get_class($this),
                $this->pluginManagerService
            ));
        }

        $class = $this->pluginManagerModel;
        return new $class($services->get($this->pluginManagerService));
    }
}
