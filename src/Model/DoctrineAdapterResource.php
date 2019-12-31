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
use Laminas\Http\Response;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DoctrineAdapterResource extends AbstractResourceListener
{
    /**
     * @var DbAdapterModel
     */
    protected $model;

    /**
     * @var array
     */
    protected $loadedModules;

    /**
     * @var null|ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param DoctrineAdapterModel $model
     * @param array $loadedModules List of loaded modules
     */
    public function __construct(DoctrineAdapterModel $model, array $loadedModules = [])
    {
        $this->model = $model;
        $this->loadedModules = $loadedModules;
    }

    /**
     * Set service locator
     *
     * @deprecated since 1.5.0, and no longer used internally.
     * @param ServiceLocatorInterface $serviceLocator
     * @return $this
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * @deprecated since 1.5.0, and no longer used internally.
     * @return null|ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param $id
     * @return DbAdapterEntity|ApiProblem
     */
    public function fetch($id)
    {
        $entity = $this->model->fetch($id);
        if (! $entity) {
            return new ApiProblem(404, 'Adapter not found');
        }
        return $entity;
    }

    /**
     * @param array $params
     * @return array|Response
     */
    public function fetchAll($params = [])
    {
        if (! isset($this->loadedModules['Laminas\ApiTools\Doctrine\Admin'])
            || ! isset($this->loadedModules['Laminas\ApiTools\Doctrine\Server'])
        ) {
            $response = new Response();
            $response->setStatusCode(204);

            return $response;
        }

        if (false === ($adapters = $this->model->fetchAll($params))) {
            return [];
        }

        return $adapters;
    }

    /**
     * @param object $data
     * @return false|DbAdapterEntity
     * @throws CreationException
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array)$data;
            if (! isset($data['doctrine_adapter_name'])) {
                throw new CreationException('Missing doctrine_adapter_name', 422);
            }

            $name = $data['doctrine_adapter_name'];
            unset($data['doctrine_adapter_name']);

            return $this->model->create($name, $data);
        }
        return false;
    }

    /**
     * @param $id
     * @param object|array $data
     * @return DbAdapterEntity|ApiProblem
     */
    public function patch($id, $data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (! is_array($data)) {
            return new ApiProblem(400, 'Invalid data provided for update');
        }

        if (empty($data)) {
            return new ApiProblem(400, 'No data provided for update');
        }

        return $this->model->update($id, $data);
    }

    /**
     * @param $id
     * @return true
     */
    public function delete($id)
    {
        $this->model->remove($id);
        return true;
    }
}
