<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\Admin\Exception;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Validator\ValidatorPluginManager;

use function array_flip;
use function array_merge;
use function array_walk;
use function get_class;
use function is_array;
use function sprintf;

class ValidatorsModel extends AbstractPluginManagerModel
{
    /** @var ValidatorMetadataModel */
    protected $metadata;

    /**
     * $pluginManager should be an instance of
     * Laminas\Validator\ValidatorPluginManager.
     */
    public function __construct(ServiceManager $pluginManager, ?ValidatorMetadataModel $metadata = null)
    {
        if (! $pluginManager instanceof ValidatorPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Laminas\Validator\ValidatorPluginManager; received "%s"',
                self::class,
                get_class($pluginManager)
            ));
        }

        if (null === $metadata) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Laminas\Validator\ValidatorMetadataModel'
                . ' as the second argument to the constructor',
                self::class
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
        array_walk($plugins, function (&$value) {
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
