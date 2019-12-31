<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;

class VersioningModelFactory
{
    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    /**
     * Already created model instances
     *
     * @var array
     */
    protected $models = array();

    /**
     * @param  ConfigResourceFactory $configFactory
     */
    public function __construct(ConfigResourceFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * @param  string $module
     * @return RpcServiceModel
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $config = $this->configFactory->factory($this->normalizeModuleName($module));
        $this->models[$module] = new VersioningModel($config);

        return $this->models[$module];
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function normalizeModuleName($name)
    {
        return str_replace('.', '\\', $name);
    }
}
