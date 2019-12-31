<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

abstract class AbstractPluginManagerController extends AbstractActionController
{
    /**
     * @var object
     */
    protected $model;

    /**
     * @var string Name of property in view model to which values are assigned
     */
    protected $property;

    /**
     * Call this method from the appropriate action method
     *
     * @return ApiProblemResponse|JsonModel
     */
    public function handleRequest()
    {
        $request = $this->getRequest();

        if ($request->getMethod() != $request::METHOD_GET) {
            return new ApiProblemResponse(
                new ApiProblem(405, 'Only the GET method is allowed for this URI')
            );
        }

        $model = new JsonModel([$this->property => $this->model->fetchAll()]);
        $model->setTerminal(true);
        return $model;
    }
}
