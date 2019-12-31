<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Model;

use Exception;
use Laminas\ApiTools\ContentValidation\Validator\DbNoRecordExists;
use Laminas\ApiTools\ContentValidation\Validator\DbRecordExists;
use Laminas\Filter\Digits;
use Laminas\Filter\StaticFilter;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Validator\StringLength;

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
            'name' => StringLength::class,
            'options' => [
                'min' => 1,
                'max' => 1,
            ],
        ],
        'unique' => [
            'name' => DbNoRecordExists::class,
            'options' => [],
        ],
        'foreign_key' => [
            'name' => DbRecordExists::class,
            'options' => [],
        ],
    ];

    /**
     * @var array
     */
    protected $filters = [
        'text' => [
            ['name' => StringTrim::class],
            ['name' => StripTags::class],
        ],
        'integer' => [
            ['name' => StripTags::class],
            ['name' => Digits::class],
        ],
    ];

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     * @throws Exception if no service locator is composed
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
        $resourceClass = sprintf(
            '%s\\V%s\\Rest\\%s\\%sResource',
            $module,
            $version,
            $resourceName,
            $resourceName
        );
        return $this->getServiceLocator()->has($resourceClass);
    }
}
