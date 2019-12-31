<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ValidatorMetadataModelFactory implements FactoryInterface
{
    /**
     * Return one of the plugin manager model instances
     *
     * @param ServiceLocatorInterface $services
     * @return ValidatorMetadataModel
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $metadata = [];
        if ($services->has('Config')) {
            $config = $services->get('Config');
            if (isset($config['validator_metadata'])) {
                $metadata = $config['validator_metadata'];
            }
        }

        return new ValidatorMetadataModel($metadata);
    }
}
