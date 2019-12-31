<?php

namespace Laminas\ApiTools\Admin\Model;

use Exception;
use Laminas\Filter\StaticFilter;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * This class is instantiated with a $config in some implementations (DbAutodiscoveryModel)
 * but this is dependent on the root service locator for the moduleHasService call below
 * and that must be injected into any class extending this abstract.
 */
abstract class AbstractAutodiscoveryModel
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $validators = [
        'text' => [
            'name' => 'Laminas\Validator\StringLength',
            'options' => [
                'min' => 1,
                'max' => 1,
            ],
        ],
        'unique' => [
            'name' => 'Laminas\ApiTools\ContentValidation\Validator\DbNoRecordExists',
            'options' => [],
        ],
        'foreign_key' => [
            'name' => 'Laminas\ApiTools\ContentValidation\Validator\DbRecordExists',
            'options' => [],
        ],
    ];

    /**
     * @var array
     */
    protected $filters = [
        'text' => [
            ['name' => 'Laminas\Filter\StringTrim'],
            ['name' => 'Laminas\Filter\StripTags'],
        ],
        'integer' => [
            ['name' => 'Laminas\Filter\StripTags'],
            ['name' => 'Laminas\Filter\Digits'],
        ],
    ];

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (! $this->serviceLocator) {
            throw new Exception('The AbstractAutodiscoveryModel must be composed with a service locator');
        }

        return $this->serviceLocator;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return AbstractAutodiscoveryModel
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Constructor
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param $module
     * @param $version
     * @param $tableName
     * @return bool
     */
    protected function moduleHasService($module, $version, $tableName)
    {
        $resourceName = StaticFilter::execute($tableName, 'WordUnderscoreToCamelCase');
        $resourceClass     = sprintf(
            '%s\\V%s\\Rest\\%s\\%sResource',
            $module,
            $version,
            $resourceName,
            $resourceName
        );
        return $this->getServiceLocator()->has($resourceClass);
    }
}
