<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\Hydrator\HydratorPluginManager;
use Laminas\ServiceManager\ServiceManager;

use function get_class;
use function sprintf;

class HydratorsModel extends AbstractPluginManagerModel
{
    /**
     * $pluginManager should be an instance of
     * Laminas\Hydrator\HydratorPluginManager.
     */
    public function __construct(ServiceManager $pluginManager)
    {
        if (! $pluginManager instanceof HydratorPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Laminas\Hydrator\HydratorPluginManager; received "%s"',
                self::class,
                get_class($pluginManager)
            ));
        }
        parent::__construct($pluginManager);
    }
}
