<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;

/**
 * Class DbAutodiscoveryController
 *
 * @package Laminas\ApiTools\Admin\Controller
 */
class DbAutodiscoveryController extends AbstractActionController
{
    /**
     * @var DbAutodiscoveryModel
     */
    protected $model;

    /**
     * Constructor
     *
     * @param DbAutodiscoveryModel $model
     */
    public function __construct(DbAutodiscoveryModel $model)
    {
        $this->model = $model;
    }

    public function discoverAction()
    {
        $module = $this->params()->fromRoute('name', false);
        $version = $this->params()->fromRoute('version', false);
        $adapter_name = urldecode($this->params()->fromRoute('adapter_name', false));

        $data = $this->model->fetchColumns($module, $version, $adapter_name);

        return new ViewModel(['payload' => $data]);
    }
}
