<?php

/**
 * @see       https://github.com/laminas-api-tools/api-tools-admin for the canonical source repository
 * @copyright https://github.com/laminas-api-tools/api-tools-admin/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas-api-tools/api-tools-admin/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;

abstract class AbstractAuthenticationController extends AbstractActionController
{
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

    /**
     * Get the API version from the Accept header
     *
     * @param  Request $request
     * @return int
     */
    protected function getVersion(Request $request)
    {
        $accept = $request->getHeader('Accept', false);

        if (! $accept) {
            return 1;
        }

        if (preg_match('/application\/vnd\.api-tools\.v(\d+)\+json/', $accept->getFieldValue(), $matches)) {
            return (int) $matches[1];
        }

        return 1;
    }
}
