<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;

class ValidatorsModel extends AbstractPluginManagerModel
{
    /**
     * @var ValidatorMetadataModel
     */
    protected $metadata;

    /**
     * $pluginManager should be an instance of
     * Laminas\Validator\ValidatorPluginManager.
     *
     * @param ServiceManager $pluginManager
     * @param ValidatorMetadataModel $metadata
     */
    public function __construct(ServiceManager $pluginManager, ValidatorMetadataModel $metadata = null)
    {
        if (! $pluginManager instanceof ValidatorPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Laminas\Validator\ValidatorPluginManager; received "%s"',
                __CLASS__,
                get_class($pluginManager)
            ));
        }

        if (null === $metadata) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Laminas\Validator\ValidatorMetadataModel'
                . ' as the second argument to the constructor',
                __CLASS__
            ));
        }

        parent::__construct($pluginManager);
        $this->metadata = $metadata;
    }

    /**
     * Retrieve all plugins
     *
     * Merges the list of plugins with the plugin metadata
     *
     * @return array
     */
    protected function getPlugins()
    {
        if (is_array($this->plugins)) {
            return $this->plugins;
        }

        $plugins = parent::getPlugins();
        $plugins = array_flip($plugins);
        $plugins = array_merge($plugins, $this->metadata->fetchAll());
        array_walk($plugins, function (& $value) {
            if (is_array($value)) {
                return;
            }
            $value = [
                'breakchainonfailure' => 'bool',
            ];
        });
        $this->plugins = $plugins;
        return $this->plugins;
    }
}
