<?php

declare(strict_types=1);

namespace LaminasTest\ApiTools\Admin\Listener;

use Laminas\ApiTools\Admin\Listener\CryptFilterListener;
use Laminas\Filter\Compress;
use Laminas\Filter\Compress\Gz;
use Laminas\Filter\Encrypt;
use Laminas\Filter\Encrypt\BlockCipher;
use Laminas\Http\Request;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\RequestInterface;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\TestCase;

class CryptFilterListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->listener   = new CryptFilterListener();
        $this->event      = new MvcEvent();
        $this->request    = $this->createMock(Request::class);
        $this->routeMatch = $this->getMockBuilder($this->getRouteMatchClass())
            ->disableOriginalConstructor(true)
            ->getMock();
        $this->event->setRequest($this->request);
    }

    protected function initRequestMethod()
    {
        $this->request->expects($this->once())
            ->method('isPut')
            ->will($this->returnValue(true));
    }

    protected function initRouteMatch()
    {
        $this->routeMatch->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('controller'), $this->equalTo(false))
            ->will($this->returnValue('Laminas\ApiTools\Admin\Controller\InputFilter'));
        $this->event->setRouteMatch($this->routeMatch);
    }

    public function testReturnsNullIfRequestIsNotAnHttpRequest()
    {
        $request = $this->createMock(RequestInterface::class);
        $this->event->setRequest($request);
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRequestMethodIsNotPut()
    {
        $this->request->expects($this->once())
            ->method('isPut')
            ->will($this->returnValue(false));
        $this->initRequestMethod();
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRouteMatchesAreNull()
    {
        $this->initRequestMethod();
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRouteMatchDoesNotContainMatchingController()
    {
        $this->initRequestMethod();
        $this->routeMatch->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('controller'), $this->equalTo(false))
            ->will($this->returnValue(false));
        $this->event->setRouteMatch($this->routeMatch);
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfNoContentNegotiationParameterDataPresent()
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfParameterDataDoesNotContainFilters()
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->event->setParam('LaminasContentNegotiationParameterData', ['foo' => 'bar']);
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsTrueIfProcessesParameterData()
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->event->setParam('LaminasContentNegotiationParameterData', ['filters' => []]);
        $this->assertTrue($this->listener->onRoute($this->event));
    }

    public function testUpdatesParameterDataIfAnyCompressionOrEncryptionFiltersDetected()
    {
        $filters = [
            [
                'name' => BlockCipher::class,
            ],
            [
                'name' => Gz::class,
            ],
        ];

        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->event->setParam('LaminasContentNegotiationParameterData', ['filters' => $filters]);
        $this->assertTrue($this->listener->onRoute($this->event));
        $data    = $this->event->getParam('LaminasContentNegotiationParameterData');
        $filters = $data['filters'];

        foreach ($filters as $filter) {
            $this->assertArrayHasKey('name', $filter);
            $this->assertArrayHasKey('options', $filter);
            $this->assertArrayHasKey('adapter', $filter['options']);

            switch ($filter['name']) {
                case Compress::class:
                    $this->assertEquals('Gz', $filter['options']['adapter']);
                    break;
                case Encrypt::class:
                    $this->assertEquals('BlockCipher', $filter['options']['adapter']);
                    break;
                default:
                    $this->fail('Unrecognized filter: ' . $filter['name']);
            }
        }
    }
}
