<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Laminas\ApiTools\Admin\Exception;
use ReturnTypeWillChange;

use function array_key_exists;
use function count;
use function in_array;
use function is_bool;
use function sprintf;

class AuthorizationEntity implements
    Countable,
    IteratorAggregate
{
    public const TYPE_ENTITY     = 'entity';
    public const TYPE_COLLECTION = 'collection';

    /** @var string[] */
    protected $allowedRestTypes = [
        self::TYPE_ENTITY,
        self::TYPE_COLLECTION,
    ];

    /** @var array<string, bool> */
    protected $defaultPrivileges = [
        'GET'    => false,
        'POST'   => false,
        'PATCH'  => false,
        'PUT'    => false,
        'DELETE' => false,
    ];

    /** @var array<string, array<string, bool>> */
    protected $servicePrivileges = [];

    public function __construct(array $services = [])
    {
        foreach ($services as $serviceName => $privileges) {
            $this->servicePrivileges[$serviceName] = $this->filterPrivileges($privileges);
        }
    }

    /** @return int */
    #[ReturnTypeWillChange]
    public function count()
    {
        return count($this->servicePrivileges);
    }

    /** @return ArrayIterator */
    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->servicePrivileges);
    }

    /** @return array<string, array<string, bool>> */
    public function getArrayCopy()
    {
        return $this->servicePrivileges;
    }

    /**
     * @param array<string, array<string, bool>> $services
     * @return void
     */
    public function exchangeArray(array $services)
    {
        foreach ($services as $serviceName => $privileges) {
            $this->servicePrivileges[$serviceName] = $this->filterPrivileges($privileges);
        }
    }

    /**
     * @param string $serviceName
     * @param string $entityOrCollection
     * @param array<string, bool>|null $privileges
     * @return $this
     */
    public function addRestService($serviceName, $entityOrCollection, ?array $privileges = null)
    {
        if (! in_array($entityOrCollection, $this->allowedRestTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided for %s; must be one of "%s" or "%s"',
                $entityOrCollection,
                __METHOD__,
                self::TYPE_ENTITY,
                self::TYPE_COLLECTION
            ));
        }
        $this->addRpcService($serviceName, sprintf('__%s__', $entityOrCollection), $privileges);
        return $this;
    }

    /**
     * @param string $serviceName
     * @param string $action
     * @param array<string, bool>|null $privileges
     * @return $this
     */
    public function addRpcService($serviceName, $action, ?array $privileges = null)
    {
        if (null === $privileges) {
            $privileges = $this->defaultPrivileges;
        }

        $serviceName                           = sprintf('%s::%s', $serviceName, $action);
        $this->servicePrivileges[$serviceName] = $this->filterPrivileges($privileges);
        return $this;
    }

    /**
     * @param string $serviceName
     * @return bool
     */
    public function has($serviceName)
    {
        return array_key_exists($serviceName, $this->servicePrivileges);
    }

    /**
     * @param string $serviceName
     * @return array<string, bool>
     * @throws Exception\InvalidArgumentException
     */
    public function get($serviceName)
    {
        if (! $this->has($serviceName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'No service by the name of "%s" has been registered',
                $serviceName
            ));
        }
        return $this->servicePrivileges[$serviceName];
    }

    /**
     * @param array<string, bool> $privileges
     * @return array<string, bool>
     */
    protected function filterPrivileges(array $privileges): array
    {
        foreach ($privileges as $httpMethod => $flag) {
            if (! array_key_exists($httpMethod, $this->defaultPrivileges)) {
                unset($privileges[$httpMethod]);
                continue;
            }
            if (! is_bool($flag)) {
                $privileges[$httpMethod] = (bool) $flag;
            }
        }
        return $privileges;
    }
}
