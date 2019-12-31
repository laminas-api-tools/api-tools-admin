<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\Hal\Entity as HalEntity;
use Laminas\ApiTools\Hal\Link\Link as HalLink;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Controller\AbstractActionController;

class DocumentationController extends AbstractActionController
{
    protected $model;

    public function __construct(DocumentationModel $docModel)
    {
        $this->model = $docModel;
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $httpMethod = $request->getMethod();
        $module = $this->params()->fromRoute('name', false);
        $controllerServiceName = $this->params()->fromRoute('controller_service_name', false);
        $controllerType = $this->params()->fromRoute('controller_type'); // rest or rpc

        $routeName = $this->deriveRouteName($this->getEvent()->getRouteMatch()->getMatchedRouteName());

        switch ($httpMethod) {
            case HttpRequest::METHOD_GET:
                $result = new HalEntity(
                    $this->model->fetchDocumentation($module, $controllerServiceName),
                    'documentation'
                );
                $self = new HalLink('self');
                $self->setRoute($routeName);
                $result->getLinks()->add($self);
                break;
            case HttpRequest::METHOD_PUT:
                $documentation = $this->bodyParams();
                $result = new HalEntity(
                    $this->model->storeDocumentation(
                        $module,
                        $controllerType,
                        $controllerServiceName,
                        $documentation,
                        true
                    ),
                    'documentation'
                );
                $self = new HalLink('self');
                $self->setRoute($routeName);
                $result->getLinks()->add($self);
                break;
            case HttpRequest::METHOD_PATCH:
                $documentation = $this->bodyParams();
                $result = new HalEntity(
                    $this->model->storeDocumentation(
                        $module,
                        $controllerType,
                        $controllerServiceName,
                        $documentation,
                        false
                    ),
                    'documentation'
                );
                $self = new HalLink('self');
                $self->setRoute($routeName);
                $result->getLinks()->add($self);
                break;
            case HttpRequest::METHOD_DELETE:
            case HttpRequest::METHOD_POST:
            default:
                return new ApiProblemResponse(
                    new ApiProblem(404, 'Unsupported method.')
                );
        }

        $e = $this->getEvent();
        $e->setParam('LaminasContentNegotiationFallback', 'HalJson');

        return new ViewModel(['payload' => $result]);
    }

    protected function deriveRouteName($route)
    {
        $matches = [];
        preg_match('/(?P<type>rpc|rest)/', $route, $matches);
        return sprintf('api-tools/api/module/%s-service/doc', $matches['type']);
    }
}
