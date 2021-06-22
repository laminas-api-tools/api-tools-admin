<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

use function defined;

use const ApiTools\VERSION;

class ApiToolsVersionController extends AbstractActionController
{
    /** @return JsonModel */
    public function indexAction()
    {
        return new JsonModel([
            'version' => defined('ApiTools\VERSION') ? VERSION : '@dev',
        ]);
    }
}
