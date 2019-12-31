<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

class HydratorsModelFactory extends AbstractPluginManagerModelFactory
{
    /**
     * @var string
     */
    protected $pluginManagerService = 'HydratorManager';

    /**
     * @var string
     */
    protected $pluginManagerModel = HydratorsModel::class;
}
