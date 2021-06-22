<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

class HydratorsModelFactory extends AbstractPluginManagerModelFactory
{
    /** @var string */
    protected $pluginManagerService = 'HydratorManager';

    /** @var string */
    protected $pluginManagerModel = HydratorsModel::class;
}
