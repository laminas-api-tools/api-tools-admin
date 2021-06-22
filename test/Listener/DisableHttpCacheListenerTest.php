<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Listener;

use Laminas\ApiTools\Admin\Listener\DisableHttpCacheListener;
use Laminas\Http\Header\GenericHeader;
use Laminas\Http\Header\GenericMultiHeader;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\MvcEvent;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DisableHttpCacheListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event      = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
        $this->request    = $this->prophesize(Request::class);
        $this->response   = $this->prophesize(Response::class);
        $this->headers    = $this->prophesize(Headers::class);
    }

    public function testListenerDoesNothingIfNoRouteMatchPresent()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->willReturn(null);

        $this->routeMatch->getParam(Argument::any())->shouldNotBeCalled();
        $this->event->getRequest()->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchNotForAdminApi()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal']);

        $this->routeMatch->getParam('is_api-tools_admin_api', false)->willReturn(false);
        $this->event->getRequest()->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRequestIsNotAGetOrHeadRequest()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal']);

        $this->routeMatch->getParam('is_api-tools_admin_api', false)->willReturn(true);
        $this->event->getRequest()->will([$this->request, 'reveal']);
        $this->request->isGet()->willReturn(false);
        $this->request->isHead()->willReturn(false);
        $this->event->getResponse()->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerInjectsCacheBustHeadersForGetRequests()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal']);

        $this->routeMatch->getParam('is_api-tools_admin_api', false)->willReturn(true);
        $this->event->getRequest()->will([$this->request, 'reveal']);
        $this->request->isGet()->willReturn(true);
        $this->request->isHead()->willReturn(false);
        $this->event->getResponse()->will([$this->response, 'reveal']);
        $this->response->getHeaders()->will([$this->headers, 'reveal']);
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Expires') {
                return false;
            }
            if ($header->getFieldValue() !== '0') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericMultiHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Cache-Control') {
                return false;
            }
            if ($header->getFieldValue() !== 'no-store, no-cache, must-revalidate') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericMultiHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Cache-Control') {
                return false;
            }
            if ($header->getFieldValue() !== 'post-check=0, pre-check=0') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeaderLine('Pragma', 'no-cache')->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerInjectsCacheBustHeadersForHeadRequests()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal']);

        $this->routeMatch->getParam('is_api-tools_admin_api', false)->willReturn(true);
        $this->event->getRequest()->will([$this->request, 'reveal']);
        $this->request->isGet()->willReturn(false);
        $this->request->isHead()->willReturn(true);
        $this->event->getResponse()->will([$this->response, 'reveal']);
        $this->response->getHeaders()->will([$this->headers, 'reveal']);
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Expires') {
                return false;
            }
            if ($header->getFieldValue() !== '0') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericMultiHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Cache-Control') {
                return false;
            }
            if ($header->getFieldValue() !== 'no-store, no-cache, must-revalidate') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericMultiHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Cache-Control') {
                return false;
            }
            if ($header->getFieldValue() !== 'post-check=0, pre-check=0') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeaderLine('Pragma', 'no-cache')->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }
}
