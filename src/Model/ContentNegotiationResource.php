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
use Laminas\InputFilter\InputFilterInterface;

class ContentNegotiationResource extends AbstractResourceListener
{
    /**
     * @var ContentNegotiationModel
     */
    protected $model;

    public function __construct(ContentNegotiationModel $model)
    {
        $this->model = $model;
    }

    /**
     * Inject the input filter.
     *
     * Primarily present for testing; input filters will be injected via event
     * normally.
     *
     * @param InputFilterInterface $inputFilter
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    public function fetch($id)
    {
        $entity = $this->model->fetch($id);
        if (! $entity) {
            return new ApiProblem(404, 'Adapter not found');
        }
        return $entity;
    }

    public function fetchAll($params = [])
    {
        return $this->model->fetchAll();
    }

    public function create($data)
    {
        $data = $this->getInputFilter()->getValues();

        if (! isset($data['content_name'])) {
            throw new CreationException('Missing content_name', 422);
        }

        $name = $data['content_name'];
        unset($data['content_name']);

        $selectors = [];
        if (isset($data['selectors'])) {
            $selectors = (array) $data['selectors'];
        }

        return $this->model->create($name, $selectors);
    }

    public function patch($id, $data)
    {
        $data = $this->getInputFilter()->getValues();

        if (empty($data) || ! array_key_exists('selectors', $data)) {
            return new ApiProblem(400, 'Invalid data provided for update');
        }

        if (empty($data['selectors'])) {
            return new ApiProblem(400, 'No data provided for update');
        }

        return $this->model->update($id, (array) $data['selectors']);
    }

    public function delete($id)
    {
        $this->model->remove($id);
        return true;
    }
}
