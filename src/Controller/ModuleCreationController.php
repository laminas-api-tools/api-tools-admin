<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\ModuleEntity;
use Laminas\ApiTools\Admin\Model\ModuleModel;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\Hal\Entity;
use Laminas\ApiTools\Hal\Link\Link;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;

class ModuleCreationController extends AbstractActionController
{
    protected $moduleModel;

    public function __construct(ModuleModel $moduleModel)
    {
        $this->moduleModel = $moduleModel;
    }

    public function apiEnableAction()
    {
        $request = $this->getRequest();

        switch ($request->getMethod()) {
            case $request::METHOD_PUT:
                $module = $this->bodyParam('module', false);
                if (! $module) {
                    return new ApiProblemResponse(
                        new ApiProblem(
                            422,
                            'Module parameter not provided',
                            'https://tools.ietf.org/html/rfc4918',
                            'Unprocessable Entity'
                        )
                    );
                }

                $result = $this->moduleModel->updateModule($module);

                if (! $result) {
                    return new ApiProblemResponse(
                        new ApiProblem(500, 'Unable to Apigilify the module')
                    );
                }

                $metadata = new ModuleEntity($module);
                $entity   = new Entity($metadata, $module);
                $entity->getLinks()->add(Link::factory([
                    'rel'   => 'self',
                    'route' => [
                        'name'   => 'api-tools/api/module',
                        'params' => ['module' => $module],
                    ],
                ]));
                return new ViewModel(['payload' => $entity]);

            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the method PUT is allowed for this URI')
                );
        }
    }

    /**
     * Set the request object manually
     *
     * Provided for testing.
     *
     * @param  Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
