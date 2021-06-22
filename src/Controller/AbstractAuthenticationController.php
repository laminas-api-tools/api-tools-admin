<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;

use function preg_match;

abstract class AbstractAuthenticationController extends AbstractActionController
{
    /**
     * Set the request object manually
     *
     * Provided for testing.
     *
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
