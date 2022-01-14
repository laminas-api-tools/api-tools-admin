<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Controller;

use Laminas\ApiTools\ApiProblem\ApiProblem;
use Laminas\ApiTools\ApiProblem\ApiProblemResponse;
use Laminas\ApiTools\Configuration\ConfigResource;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;

use function array_shift;
use function explode;
use function json_decode;
use function strtolower;
use function trim;

abstract class AbstractConfigController extends AbstractActionController
{
    /** @return array */
    abstract public function getConfig();

    /** @return array|ApiProblemResponse */
    public function processAction()
    {
        /** @var Request $request */
        $request     = $this->getRequest();
        $headers     = $request->getHeaders();
        $accept      = $this->getHeaderType($headers, 'accept');
        $contentType = $this->getHeaderType($headers, 'content-type');
        $returnTrees = 'application/json' !== $accept;

        $config = $this->getConfig();
        if (! $config instanceof ConfigResource) {
            return $config;
        }

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                return $config->fetch($returnTrees);
            case $request::METHOD_PATCH:
                $submitTrees = 'application/json' !== $contentType;
                $params      = $this->getBodyParams($submitTrees);
                $result      = $config->patch($params, $submitTrees);

                // If same accept and content-type, return the result directly
                if ($submitTrees === $returnTrees) {
                    return $result;
                }

                // If tree was submitted, but not Accepted, create dot-separated values
                if ($submitTrees && ! $returnTrees) {
                    return $config->traverseArray($params);
                }

                // If tree was not submitted, but is Accepted, create nested values
                $return = [];
                foreach ($params as $key => $value) {
                    $config->createNestedKeyValuePair($return, $key, $value);
                }
                return $return;
            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the methods GET and PATCH are allowed for this URI')
                );
        }
    }

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
     * Get the body params
     *
     * The body params plugin only knows about application/json, not our custom
     * vendor type; if using our custom vendor type, parse the content.
     *
     * @param  bool $useTrees
     * @return array
     */
    protected function getBodyParams($useTrees)
    {
        if (! $useTrees) {
            return $this->bodyParams();
        }

        return json_decode($this->getRequest()->getContent(), true);
    }

    /**
     * Get the media type from a given header
     *
     * @param Headers $headers
     * @param  string $header
     * @return string
     */
    protected function getHeaderType($headers, $header)
    {
        if (! $headers->has($header)) {
            return 'application/json';
        }

        $accept = $headers->get($header);
        $value  = $accept->getFieldValue();
        $value  = explode(';', $value, 2);
        $accept = array_shift($value);
        $accept = strtolower(trim($accept));
        switch ($accept) {
            case 'application/json':
            case 'application/vnd.laminas-api-tools.v1.config+json':
            // @todo Remove Legacy Zend Framework accept header
            case 'application/vnd.laminascampus.v1.config+json':
            case 'application/vnd.zfcampus.v1.config+json':
                return $accept;
            default:
                return 'application/json';
        }
    }
}
