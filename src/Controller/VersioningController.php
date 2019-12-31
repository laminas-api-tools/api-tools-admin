<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Exception;
use Laminas\ApiTools\Admin\Model\ModuleVersioningModelFactoryInterface;
use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\View\ApiProblemModel;
use Laminas\Mvc\Controller\AbstractActionController;

class VersioningController extends AbstractActionController
{
    protected $modelFactory;

    public function __construct(ModuleVersioningModelFactoryInterface $modelFactory)
    {
        $this->modelFactory = $modelFactory;
    }

    public function defaultVersionAction()
    {
        $module = $this->bodyParam('module', false);
        if (! $module) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Module parameter not provided',
                    'https://tools.ietf.org/html/rfc4918',
                    'Unprocessable Entity'
                )
            );
        }

        $version = $this->bodyParam('version', false);

        if (! $version || ! is_numeric($version)) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Missing or invalid version',
                    'https://tools.ietf.org/html/rfc4918',
                    'Unprocessable Entity'
                )
            );
        }

        $model = $this->modelFactory->factory($module);

        if ($model->setDefaultVersion($version)) {
            return ['success' => true, 'version' => $version];
        } else {
            return new ApiProblemModel(
                new ApiProblem(500, 'An unexpected error occurred while attempting to set the default version')
            );
        }
    }

    public function versioningAction()
    {
        $module = $this->bodyParam('module', false);
        if (! $module) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Module parameter not provided',
                    'https://tools.ietf.org/html/rfc4918',
                    'Unprocessable Entity'
                )
            );
        }

        $model = $this->modelFactory->factory($module);

        $version = $this->bodyParam('version', false);
        if (! $version) {
            try {
                $versions = $model->getModuleVersions();
            } catch (Exception\ExceptionInterface $ex) {
                return new ApiProblemModel(new ApiProblem(404, 'Module not found'));
            }
            if (! $versions) {
                return new ApiProblemModel(new ApiProblem(500, 'Module cannot be versioned'));
            }
            sort($versions);
            $version = array_pop($versions);
            $version += 1;
        }


        try {
            $model->createVersion($version);
        } catch (Exception\InvalidArgumentException $ex) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Invalid module and/or version',
                    'https://tools.ietf.org/html/rfc4918',
                    'Unprocessable Entity'
                )
            );
        }

        return [
            'success' => true,
            'version' => $version,
        ];
    }
}
