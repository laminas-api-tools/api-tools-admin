<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\DocumentationModel;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\Hal\Entity as HalEntity;
use Laminas\ApiTools\Hal\Link\Link as HalLink;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\Controller\AbstractActionController;

use function preg_match;
use function sprintf;

class DocumentationController extends AbstractActionController
{
    /** @var DocumentationModel */
    protected $model;

    public function __construct(DocumentationModel $docModel)
    {
        $this->model = $docModel;
    }

    /** @return ViewModel|ApiProblemResponse */
    public function indexAction()
    {
        $request               = $this->getRequest();
        $httpMethod            = $request->getMethod();
        $module                = $this->params()->fromRoute('name', false);
        $controllerServiceName = $this->params()->fromRoute('controller_service_name', false);
        $controllerType        = $this->params()->fromRoute('controller_type'); // rest or rpc

        $routeName = $this->deriveRouteName($this->getEvent()->getRouteMatch()->getMatchedRouteName());

        switch ($httpMethod) {
            case HttpRequest::METHOD_GET:
                $result = new HalEntity(
                    $this->model->fetchDocumentation($module, $controllerServiceName),
                    'documentation'
                );
                $self   = new HalLink('self');
                $self->setRoute($routeName);
                $result->getLinks()->add($self);
                break;
            case HttpRequest::METHOD_PUT:
                $documentation = $this->bodyParams();
                $result        = new HalEntity(
                    $this->model->storeDocumentation(
                        $module,
                        $controllerType,
                        $controllerServiceName,
                        $documentation,
                        true
                    ),
                    'documentation'
                );
                $self          = new HalLink('self');
                $self->setRoute($routeName);
                $result->getLinks()->add($self);
                break;
            case HttpRequest::METHOD_PATCH:
                $documentation = $this->bodyParams();
                $result        = new HalEntity(
                    $this->model->storeDocumentation(
                        $module,
                        $controllerType,
                        $controllerServiceName,
                        $documentation,
                        false
                    ),
                    'documentation'
                );
                $self          = new HalLink('self');
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

    protected function deriveRouteName(string $route): string
    {
        $matches = [];
        preg_match('/(?P<type>rpc|rest)/', $route, $matches);
        return sprintf('api-tools/api/module/%s-service/doc', $matches['type']);
    }
}
