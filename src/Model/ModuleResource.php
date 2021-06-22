<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Exception;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\ApiTools\Rest\Exception\CreationException;

use function is_object;
use function preg_match;
use function str_replace;

class ModuleResource extends AbstractResourceListener
{
    /** @var ModuleModel */
    protected $modules;

    /** @var string */
    protected $modulePath = '.';

    /** @var ModulePathSpec */
    protected $modulePathSpec;

    public function __construct(ModuleModel $modules, ModulePathSpec $pathSpec)
    {
        $this->modules        = $modules;
        $this->modulePathSpec = $pathSpec;
    }

    /**
     * Set path to use when creating new modules
     *
     * @param  string $path
     * @return $this
     */
    public function setModulePath($path)
    {
        /*
         * maintain backwards compatibility
         * NOTE: modulePath in this case, is really the application path
         */
        $this->modulePathSpec->setApplicationPath($path);

        return $this;
    }

    /**
     * Create a new API-First enabled module
     *
     * @param array|object $data
     * @return ModuleEntity
     * @throws CreationException
     * @throws Exception
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (! isset($data['name'])) {
            throw new CreationException('Missing module name');
        }

        $version = $data['version'] ?? 1;
        $name    = str_replace(['.', '/'], '\\', $data['name']);
        if (! preg_match('#^[a-zA-Z][a-zA-Z0-9_]*(\\\\[a-zA-Z][a-zA-Z0-9_]*)*$#', $name)) {
            throw new CreationException('Invalid module name; must be a valid PHP namespace name');
        }

        if (false === $this->modules->createModule($name, $this->modulePathSpec)) {
            throw new CreationException('Unable to create module; check your paths and permissions');
        }

        $metadata = new ModuleEntity($name);
        $metadata->exchangeArray([
            'versions' => [$version],
        ]);
        return $metadata;
    }

    /**
     * Fetch module metadata
     *
     * @param  string $id
     * @return ModuleEntity|ApiProblem
     */
    public function fetch($id)
    {
        $module = $this->modules->getModule($id);
        if (! $module instanceof ModuleEntity) {
            return new ApiProblem(404, 'Module not found or is not api-tools-enabled');
        }
        return $module;
    }

    /**
     * Fetch metadata for all API-First enabled modules
     *
     * @param  array $params
     * @return array
     */
    public function fetchAll($params = [])
    {
        return $this->modules->getModules();
    }

    /**
     * Delete a module (and, optionally, all code within it)
     *
     * @param  string $id
     * @return bool|ApiProblem
     */
    public function delete($id)
    {
        $request   = $this->getEvent()->getRequest();
        $recursive = $request->getQuery('recursive', false);

        $module = $this->modules->getModule($id);
        if (! $module instanceof ModuleEntity) {
            return new ApiProblem(404, 'Module not found or is not api-tools-enabled');
        }

        $name = $module->getName();
        return $this->modules->deleteModule($name, $this->modulePath, $recursive);
    }
}
