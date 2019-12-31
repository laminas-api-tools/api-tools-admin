<?php

namespace Laminas\ApiTools\Admin\Model;

use Laminas\Filter\StaticFilter;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

abstract class AbstractAutodiscoveryModel implements ServiceLocatorAwareInterface
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
    protected $validators = array(
        'text' => array(
            'name' => 'Laminas\Validator\StringLength',
            'options' => array(
                'min' => 1,
                'max' => 1,
            ),
        ),
        'unique' => array(
            'name' => 'Laminas\ApiTools\ContentValidation\Validator\DbNoRecordExists',
            'options' => array(),
        ),
        'foreign_key' => array(
            'name' => 'Laminas\ApiTools\ContentValidation\Validator\DbRecordExists',
            'options' => array(),
        ),
    );

    /**
     * @var array
     */
    protected $filters = array(
        'text' => array(
            array('name' => 'Laminas\Filter\StringTrim'),
            array('name' => 'Laminas\Filter\StripTags'),
        ),
        'integer' => array(
            array('name' => 'Laminas\Filter\StripTags'),
            array('name' => 'Laminas\Filter\Digits'),
        ),
    );

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
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
