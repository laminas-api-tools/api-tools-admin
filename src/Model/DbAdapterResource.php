<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Model;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\Rest\AbstractResourceListener;
use Laminas\ApiTools\Rest\Exception\CreationException;

use function is_array;
use function is_object;

class DbAdapterResource extends AbstractResourceListener
{
    /** @var DbAdapterModel */
    protected $model;

    public function __construct(DbAdapterModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param int|string $id
     * @return ApiProblem|DbAdapterEntity
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
     * @return array
     */
    public function fetchAll($params = [])
    {
        return $this->model->fetchAll();
    }

    /**
     * @param array|object $data
     * @return DbAdapterEntity
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (! isset($data['adapter_name'])) {
            throw new CreationException('Missing adapter_name', 422);
        }

        $name = $data['adapter_name'];
        unset($data['adapter_name']);

        return $this->model->create($name, $data);
    }

    /**
     * @param int|string $id
     * @param array|object $data
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
     * @param int|string $id
     * @return bool
     */
    public function delete($id)
    {
        $this->model->remove($id);
        return true;
    }
}
