<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\ApiTools\Configuration\ResourceFactory;

use function dirname;
use function file_exists;

class ModuleVersioningModelFactory implements ModuleVersioningModelFactoryInterface
{
    /** @var ResourceFactory */
    private $configFactory;

    /** @var ModulePathSpec */
    private $moduleUtils;

    /** @var ModuleVersioningModel[] */
    private $models = [];

    public function __construct(ResourceFactory $configFactory, ModulePathSpec $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils   = $moduleUtils;
    }

    /**
     * Create service
     *
     * @param string $module
     * @return ModuleVersioningModel
     */
    public function factory($module)
    {
        $moduleName = $this->moduleUtils->normalizeModuleName($module);

        if (! isset($this->models[$moduleName])) {
            $config     = $this->configFactory->factory($moduleName);
            $docsConfig = $this->getDocsConfig($moduleName);

            $this->models[$moduleName] = ModuleVersioningModel::createWithPathSpec(
                $moduleName,
                $this->moduleUtils,
                $config,
                $docsConfig
            );
        }

        return $this->models[$moduleName];
    }

    /**
     * @param string $module
     * @return null|ConfigResource
     */
    protected function getDocsConfig($module)
    {
        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
        $docConfigPath    = dirname($moduleConfigPath) . '/documentation.config.php';
        if (! file_exists($docConfigPath)) {
            return null;
        }
        $documentation = include $docConfigPath;
        return $this->configFactory->createConfigResource($documentation, $docConfigPath);
    }
}
