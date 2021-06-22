<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Exception;
use Laminas\ApiTools\Admin\Model\DbAutodiscoveryModel;
use Laminas\ApiTools\ContentNegotiation\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;

use function urldecode;

class DbAutodiscoveryController extends AbstractActionController
{
    /** @var DbAutodiscoveryModel */
    protected $model;

    /**
     * Constructor
     */
    public function __construct(DbAutodiscoveryModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return ViewModel
     * @throws Exception
     */
    public function discoverAction()
    {
        $module      = $this->params()->fromRoute('name', false);
        $version     = $this->params()->fromRoute('version', false);
        $adapterName = urldecode($this->params()->fromRoute('adapter_name', false));

        $data = $this->model->fetchColumns($module, $version, $adapterName);

        return new ViewModel(['payload' => $data]);
    }
}
