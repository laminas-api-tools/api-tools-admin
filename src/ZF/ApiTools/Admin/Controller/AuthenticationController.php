<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\AuthenticationModel;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\Hal\Link\Link;
use Laminas\ApiTools\Hal\Resource;
use Laminas\Mvc\Controller\AbstractActionController;

class AuthenticationController extends AbstractActionController
{
    protected $model;

    public function __construct(AuthenticationModel $model)
    {
        $this->model = $model;
    }

    public function authenticationAction()
    {
        $request = $this->getRequest();

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                $entity = $this->model->fetch();
                if (!$entity) {
                    $response = $this->getResponse();
                    $response->setStatusCode(204);
                    return $response;
                }
                break;
            case $request::METHOD_POST:
                $entity = $this->model->create($this->bodyParams());
                $response = $this->getResponse();
                $response->setStatusCode(201);
                $response->getHeaders()->addHeaderLine(
                    'Location',
                    $this->plugin('hal')->createLink('api-tools-admin/api/authentication')
                );
                break;
            case $request::METHOD_PATCH:
                $entity = $this->model->update($this->bodyParams());
                break;
            case $request::METHOD_DELETE:
                if ($this->model->remove()) {
                    return $this->getResponse()->setStatusCode(204);
                }
                return new ApiProblemResponse(
                    new ApiProblem(404, 'No authentication configuration found')
                );
            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the methods GET, POST, PATCH, and DELETE are allowed for this URI')
                );
        }

        $resource = new Resource($entity, null);
        $resource->getLinks()->add(Link::factory(array(
            'rel' => 'self',
            'route' => 'api-tools-admin/api/authentication',
        )));
        $model = new ViewModel(array('payload' => $resource));
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Set the request object manually
     *
     * Provided for testing.
     *
     * @param  Request $request
     * @return self
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
