<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\ApiTools\Rest\Exception\CreationException;

class ModuleResource extends AbstractResourceListener
{
    /**
     * @var ModuleModel
     */
    protected $modules;

    /**
     * @var string
     */
    protected $modulePath = '.';

    /**
     * @param ModuleModel $modules
     */
    public function __construct(ModuleModel $modules)
    {
        $this->modules = $modules;
    }

    /**
     * Set path to use when creating new modules
     *
     * @param  string $path
     * @return self
     * @throws InvalidArgumentException for invalid paths
     */
    public function setModulePath($path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid module path "%s"; does not exist',
                $path
            ));
        }
        $this->modulePath = $path;
        return $this;
    }

    /**
     * Create a new API-First enabled module
     *
     * @param  array|object $data
     * @return ModuleEntity
     * @throws CreationException
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!isset($data['name'])) {
            throw new CreationException('Missing module name');
        }

        $version = isset($data['version']) ? $data['version'] : 1;
        $name    = $data['name'];
        $name    = str_replace('.', '\\', $name);
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\\\+[a-zA-Z][a-zA-Z0-9_]*)?$/', $name)) {
            throw new CreationException('Invalid module name; must be a valid PHP namespace name');
        }

        if (false === $this->modules->createModule($name, $this->modulePath, $version)) {
            throw new CreationException('Unable to create module; check your paths and permissions');
        }

        $metadata = new ModuleEntity($name);
        $metadata->exchangeArray(array(
            'versions' => array($version),
        ));
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
        if (!$module instanceof ModuleEntity) {
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
    public function fetchAll($params = array())
    {
        return $this->modules->getModules();
    }

    /**
     * Delete a module (and, optionally, all code within it)
     *
     * @param  string $id
     * @return bool
     */
    public function delete($id)
    {
        $request = $this->getEvent()->getRequest();
        $recursive = $request->getQuery('recursive', false);

        $module = $this->modules->getModule($id);
        if (!$module instanceof ModuleEntity) {
            return new ApiProblem(404, 'Module not found or is not api-tools-enabled');
        }

        $name = $module->getName();
        return $this->modules->deleteModule($name, $this->modulePath, $recursive);
    }
}
