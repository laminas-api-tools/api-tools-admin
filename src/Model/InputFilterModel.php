<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Configuration\Exception\InvalidArgumentException as InvalidArgumentConfiguration;
use Laminas\ApiTools\Configuration\ResourceFactory as ConfigResourceFactory;

class InputFilterModel
{
    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    /**
     * $validatorPlugins should typically be an instance of
     * Laminas\Validator\ValidatorPluginManager.
     *
     * @param ConfigResourceFactory $configFactory
     */
    public function __construct(ConfigResourceFactory $configFactory)
    {
        $this->configFactory = $configFactory;
    }

    /**
     * Get the validators of a specific module and controller
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputFilterName
     * @return false|array|InputFilterEntity
     */
    public function fetch($module, $controller, $inputFilterName = null)
    {
        return $this->getInputFilter($module, $controller, $inputFilterName);
    }

    /**
     * Update a specific controller with a new inputfilter (validator)
     *
     * @param  string $module
     * @param  string $controller
     * @param  array $inputFilter
     * @return false|InputFilterEntity
     */
    public function update($module, $controller, $inputFilter)
    {
        return $this->addInputFilter($module, $controller, $inputFilter);
    }

    /**
     * Remove the named input
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputname
     * @return bool
     */
    public function remove($module, $controller, $inputname)
    {
        return $this->removeInputFilter($module, $controller, $inputname);
    }

    /**
     * Get input filter of a module and controller
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputFilterName
     * @return false|InputFilterCollection|InputFilterEntity
     */
    protected function getInputFilter($module, $controller, $inputFilterName = null)
    {
        $configModule   = $this->configFactory->factory($module);
        $config         = $configModule->fetch(true);
        $collectionType = $this->getCollectionType($controller);
        $entityType     = $this->getEntityType($controller);

        if (! isset($config['api-tools-content-validation'][$controller]['input_filter'])) {
            return new $collectionType();
        }

        $validator = $config['api-tools-content-validation'][$controller]['input_filter'];
        if (! isset($config['input_filter_specs'])
            || ! is_array($config['input_filter_specs'])
            || ! array_key_exists($validator, $config['input_filter_specs'])
        ) {
            return false;
        }

        if ($inputFilterName && $inputFilterName !== $validator) {
            return false;
        }

        // Retrieving the input filter by name
        if ($inputFilterName && $inputFilterName === $validator) {
            $inputFilter = new $entityType($config['input_filter_specs'][$inputFilterName]);
            $inputFilter['input_filter_name'] = $inputFilterName;
            return $inputFilter;
        }

        // Retrieving a collection
        $collection  = new $collectionType();
        $inputFilter = new $entityType($config['input_filter_specs'][$validator]);
        $inputFilter['input_filter_name'] = $validator;
        $collection->enqueue($inputFilter);
        return $collection;
    }

    /**
     * Add input filter
     *
     * @param  string $module
     * @param  string $controller
     * @param  array  $inputFilter
     * @param  string $validatorName
     * @return array|bool
     */
    protected function addInputFilter($module, $controller, $inputFilter, $validatorName = null)
    {
        if (! $this->controllerExists($module, $controller)) {
            return false;
        }

        $configModule = $this->configFactory->factory($module);
        $config       = $configModule->fetch(true);

        if (! isset($config['api-tools-content-validation'][$controller])) {
            $validatorName = $validatorName ?: $this->generateValidatorName($controller);
            $config = $configModule->patchKey(
                ['api-tools-content-validation', $controller, 'input_filter'],
                $validatorName
            );
        }

        $validator = $config['api-tools-content-validation'][$controller]['input_filter'];

        if (! isset($config['input_filter_specs'])) {
            $config['input_filter_specs'] = [];
        }

        if (! isset($config['input_filter_specs'][$validator])) {
            $config['input_filter_specs'][$validator] = [];
        }

        $config['input_filter_specs'][$validator] = $inputFilter;

        $updated = $configModule->patchKey(['input_filter_specs', $validator], $inputFilter);
        if (! is_array($updated)) {
            return false;
        }

        $entityType = $this->getEntityType($controller);
        $return = new $entityType($updated['input_filter_specs'][$validator]);
        $return['input_filter_name'] = $validator;
        return $return;
    }

    /**
     * Remove input filter
     *
     * @param  string $module
     * @param  string $controller
     * @param  string $inputFilterName
     * @return bool
     */
    protected function removeInputFilter($module, $controller, $inputFilterName)
    {
        if (! $this->controllerExists($module, $controller)) {
            return false;
        }

        $configModule = $this->configFactory->factory($module);
        $config       = $configModule->fetch(true);
        $validator    = $config['api-tools-content-validation'][$controller]['input_filter'];

        if (! isset($config['input_filter_specs'][$validator])) {
            return false;
        }

        if ($inputFilterName && $inputFilterName !== $validator) {
            return false;
        }

        unset($config['input_filter_specs'][$validator]);
        unset($config['api-tools-content-validation'][$controller]['input_filter']);

        if (empty($config['input_filter_specs'])) {
            unset($config['input_filter_specs']);
        }

        if (empty($config['api-tools-content-validation'][$controller])) {
            unset($config['api-tools-content-validation'][$controller]);
        }

        if (empty($config['api-tools-content-validation'])) {
            unset($config['api-tools-content-validation']);
        }

        return ($configModule->patch($config) != false);
    }

    /**
     * Generates the validator name based on controller name
     *
     * @param string $controller
     * @return string
     */
    protected function generateValidatorName($controller)
    {
        if (strtolower(substr($controller, -11)) === '\controller') {
            return substr($controller, 0, strlen($controller) - 11) . '\Validator';
        }
        return $controller . '\Validator';
    }

    /**
     * Check if the module exists
     *
     * @param  string $module
     * @return bool
     */
    public function moduleExists($module)
    {
        try {
            $this->configFactory->factory($module);
        } catch (InvalidArgumentConfiguration $e) {
            return false;
        }
        return true;
    }

    /**
     * Check if a module and controller exists
     *
     * @param  string $module
     * @param  string $controller
     * @return bool
     */
    public function controllerExists($module, $controller)
    {
        try {
            $configModule = $this->configFactory->factory($module);
        } catch (InvalidArgumentConfiguration $e) {
            return false;
        }

        $config = $configModule->fetch(true);

        if (isset($config['api-tools-rest'])
            && array_key_exists($controller, $config['api-tools-rest'])
        ) {
            return true;
        }

        if (isset($config['api-tools-rpc'])
            && array_key_exists($controller, $config['api-tools-rpc'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Determine the collection class to use
     *
     * @param string $controller
     * @return string
     */
    protected function getCollectionType($controller)
    {
        if (strstr($controller, '\\Rest\\')) {
            return RestInputFilterCollection::class;
        }

        if (strstr($controller, '\\Rpc\\')) {
            return RpcInputFilterCollection::class;
        }

        return InputFilterCollection::class;
    }

    /**
     * Determine the entity class to use
     *
     * @param string $controller
     * @return string
     */
    protected function getEntityType($controller)
    {
        if (strstr($controller, '\\Rest\\')) {
            return RestInputFilterEntity::class;
        }

        if (strstr($controller, '\\Rpc\\')) {
            return RpcInputFilterEntity::class;
        }

        return InputFilterEntity::class;
    }
}
