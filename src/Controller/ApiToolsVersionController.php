<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class ApiToolsVersionController extends AbstractActionController
{
    public function indexAction()
    {
        return new JsonModel([
            'version' => defined('ApiTools\VERSION') ? \ApiTools\VERSION : '@dev',
        ]);
    }
}
