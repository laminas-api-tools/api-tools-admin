<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\Hydrator\HydratorPluginManager;
use Laminas\ServiceManager\ServiceManager;

class HydratorsModel extends AbstractPluginManagerModel
{
    /**
     * $pluginManager should be an instance of
     * Laminas\Hydrator\HydratorPluginManager.
     *
     * @param ServiceManager $pluginManager
     */
    public function __construct(ServiceManager $pluginManager)
    {
        if (! $pluginManager instanceof HydratorPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Laminas\Hydrator\HydratorPluginManager; received "%s"',
                __CLASS__,
                get_class($pluginManager)
            ));
        }
        parent::__construct($pluginManager);
    }
}
