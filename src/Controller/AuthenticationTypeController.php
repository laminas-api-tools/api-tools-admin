<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\ApiTools\MvcAuth\Authentication\DefaultAuthenticationListener as AuthListener;

class AuthenticationTypeController extends AbstractAuthenticationController
{
    /**
     * @var AuthListener
     */
    protected $authListener;

    /**
     * @param AuthListener $authListener
     */
    public function __construct(AuthListener $authListener)
    {
        $this->authListener = $authListener;
    }

    /**
     * Get the authentication type list
     * Since Laminas API Tools 1.1
     *
     */
    public function authTypeAction()
    {
        $request = $this->getRequest();
        $version = $this->getVersion($request);

        switch ($version) {
            case 1:
                return new ApiProblemResponse(
                    new ApiProblem(406, 'This API is supported starting from version 2')
                );
            case 2:
                if ($request->getMethod() !== $request::METHOD_GET) {
                    $response = new ApiProblemResponse(
                        new ApiProblem(405, 'Only the method GET is allowed for this URI')
                    );
                    $response->getHeaders()->addHeaderLine('Allow', 'GET');
                    return $response;
                }

                return $this->createAdapterCollection();
            default:
                return new ApiProblemResponse(
                    new ApiProblem(406, 'The API version specified is not supported')
                );
        }
    }

    /**
     * Create a collection of adapters.
     *
     * @return ViewModel
     */
    private function createAdapterCollection()
    {
        $adapters = $this->authListener->getAuthenticationTypes();
        return $this->createViewModel($adapters);
    }

    /**
     * Create a view model with the given adapters to indicate authentication types available.
     *
     * @param array $adapters
     * @return ViewModel
     */
    private function createViewModel($adapters)
    {
        $model = new ViewModel([
            'auth-types' => $adapters,
        ]);
        $model->setTerminal(true);
        return $model;
    }
}
