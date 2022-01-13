<?php

declare(strict_types=1);

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

use function sprintf;

/**
 * This class is instantiated with a $config in some implementations (DbAutodiscoveryModel)
 * but this is dependent on the root service locator for the moduleHasService call below
 * and that must be injected into any class extending this abstract.
 */
abstract class AbstractAutodiscoveryModel
{
    /** @var null|ServiceLocatorInterface */
    protected $serviceLocator;

    /** @var array */
    protected $config;

    /** @var array */
    protected $validators = [
        'text'        => [
            'name'    => StringLength::class,
            'options' => [
                'min' => 1,
                'max' => 1,
            ],
        ],
        'unique'      => [
            'name'    => DbNoRecordExists::class,
            'options' => [],
        ],
        'foreign_key' => [
            'name'    => DbRecordExists::class,
            'options' => [],
        ],
    ];

    /** @var array */
    protected $filters = [
        'text'    => [
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
     * @throws Exception If no service locator is composed.
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
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param string $module
     * @param string|int $version
     * @param string $tableName
     * @return bool
     * @throws Exception
     */
    protected function moduleHasService($module, $version, $tableName)
    {
        $resourceName  = StaticFilter::execute($tableName, 'WordUnderscoreToCamelCase');
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
