<?php

declare(strict_types=1);

namespace Laminas\ApiTools\Admin\Listener;

use Laminas\ApiTools\Admin\Controller\InputFilter;
use Laminas\ApiTools\ContentNegotiation\ParameterDataContainer;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Filter\Compress\CompressionAlgorithmInterface;
use Laminas\Filter\Encrypt\EncryptionAlgorithmInterface;
use Laminas\Mvc\MvcEvent;
use ReflectionClass;
use ReflectionException;

use function method_exists;
use function strrpos;
use function substr;

class CryptFilterListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        // Trigger between content negotiation (-625) and content validation (-650)
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], -630);
    }

    /**
     * Adjust the filter options for Crypt filter adapters
     *
     * @return void|true
     * @throws ReflectionException
     */
    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();
        if (
            ! method_exists($request, 'isPut')
            || ! $request->isPut()
        ) {
            // Not an HTTP request, or not a PUT request; nothing to do
            return;
        }

        $matches = $e->getRouteMatch();
        if (! $matches) {
            // No route matches; nothing to do
            return;
        }

        $controller = $matches->getParam('controller', false);
        if ($controller !== InputFilter::class) {
            // Not the InputFilter controller; nothing to do
            return;
        }

        $data = $e->getParam('LaminasContentNegotiationParameterData', false);
        if (! $data) {
            // No data; nothing to do
            return;
        }

        if ($data instanceof ParameterDataContainer) {
            $data = $data->getBodyParams();
        }

        if (! isset($data['filters'])) {
            // No filters passed; nothing to do
            return;
        }

        foreach ($data['filters'] as $key => $filter) {
            if (! isset($filter['name'])) {
                continue;
            }

            $filter = $filter['name'];
            $class  = new ReflectionClass($filter);

            // If filter implements CompressionAlgorithmInterface or EncryptionAlgorithmInterface,
            // we change the filter's name to the parent, and we add the adapter param to filter's name.
            if (
                $class->implementsInterface(CompressionAlgorithmInterface::class)
                || $class->implementsInterface(EncryptionAlgorithmInterface::class)
            ) {
                $name                                        = substr($filter, 0, strrpos($filter, '\\'));
                $adapter                                     = substr($filter, strrpos($filter, '\\') + 1);
                $data['filters'][$key]['name']               = $name;
                $data['filters'][$key]['options']['adapter'] = $adapter;
            }
        }

        // Inject altered data back into event
        $e->setParam('LaminasContentNegotiationParameterData', $data);
        return true;
    }
}
