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
use Laminas\Mvc\Router\RouteMatch as V2RouteMatch;
use Laminas\Router\RouteMatch;
use Laminas\Stdlib\RequestInterface;
use LaminasTest\ApiTools\Admin\RouteAssetsTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CryptFilterListenerTest extends TestCase
{
    use RouteAssetsTrait;

    /** @var CryptFilterListener */
    private $listener;
    /** @var MvcEvent */
    private $event;
    /** @var MockObject|Request */
    private $request;
    /** @var MockObject|V2RouteMatch|RouteMatch */
    private $routeMatch;

    public function setUp(): void
    {
        $this->listener   = new CryptFilterListener();
        $this->event      = new MvcEvent();
        $this->request    = $this->createMock(Request::class);
        $this->routeMatch = $this->getMockBuilder($this->getRouteMatchClass())
            ->disableOriginalConstructor()
            ->getMock();
        $this->event->setRequest($this->request);
    }

    protected function initRequestMethod(): void
    {
        $this->request->expects($this->once())
            ->method('isPut')
            ->will($this->returnValue(true));
    }

    protected function initRouteMatch(): void
    {
        $this->routeMatch->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('controller'), $this->equalTo(false))
            ->will($this->returnValue('Laminas\ApiTools\Admin\Controller\InputFilter'));
        $this->event->setRouteMatch($this->routeMatch);
    }

    public function testReturnsNullIfRequestIsNotAnHttpRequest(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $this->event->setRequest($request);
        self::assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRequestMethodIsNotPut(): void
    {
        $this->request->expects($this->once())
            ->method('isPut')
            ->will($this->returnValue(false));
        $this->initRequestMethod();
        self::assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRouteMatchesAreNull(): void
    {
        $this->initRequestMethod();
        self::assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRouteMatchDoesNotContainMatchingController(): void
    {
        $this->initRequestMethod();
        $this->routeMatch->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('controller'), $this->equalTo(false))
            ->will($this->returnValue(false));
        $this->event->setRouteMatch($this->routeMatch);
        self::assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfNoContentNegotiationParameterDataPresent(): void
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        self::assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfParameterDataDoesNotContainFilters(): void
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->event->setParam('LaminasContentNegotiationParameterData', ['foo' => 'bar']);
        self::assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsTrueIfProcessesParameterData(): void
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->event->setParam('LaminasContentNegotiationParameterData', ['filters' => []]);
        self::assertTrue($this->listener->onRoute($this->event));
    }

    public function testUpdatesParameterDataIfAnyCompressionOrEncryptionFiltersDetected(): void
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
        self::assertTrue($this->listener->onRoute($this->event));
        $data    = $this->event->getParam('LaminasContentNegotiationParameterData');
        $filters = $data['filters'];

        foreach ($filters as $filter) {
            self::assertArrayHasKey('name', $filter);
            self::assertArrayHasKey('options', $filter);
            self::assertArrayHasKey('adapter', $filter['options']);

            switch ($filter['name']) {
                case Compress::class:
                    self::assertEquals('Gz', $filter['options']['adapter']);
                    break;
                case Encrypt::class:
                    self::assertEquals('BlockCipher', $filter['options']['adapter']);
                    break;
                default:
                    $this->fail('Unrecognized filter: ' . $filter['name']);
            }
        }
    }
}
