<?php

namespace Thormeier\BreadcrumbBundle\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Thormeier\BreadcrumbBundle\Provider\BreadcrumbProvider;

/**
 * Provider class test
 */
class BreadcrumbProviderTest extends TestCase
{
    const MODEL_CLASS = 'Thormeier\BreadcrumbBundle\Model\Breadcrumb';

    const COLLECTION_CLASS = 'Thormeier\BreadcrumbBundle\Model\BreadcrumbCollection';

    private RequestEvent $responseEvent;

    private ParameterBag $requestAttributes;

    private BreadcrumbProvider $provider;

    /**
     * Set up the whole
     */
    protected function setUp(): void
    {
        $this->requestAttributes = $this->getMockBuilder(ParameterBag::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->attributes = $this->requestAttributes;

        $this->responseEvent = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRequestType', 'getRequest'])
            ->getMock();
        $this->responseEvent->expects($this->any())
            ->method('getRequest')
            ->willReturn($request);
        $this->responseEvent->expects($this->any())
            ->method('getRequestType')
            ->willReturn(HttpKernelInterface::MAIN_REQUEST);

        $this->provider = new BreadcrumbProvider(self::MODEL_CLASS, self::COLLECTION_CLASS);
    }

    /**
     * Tests the outcome if there are no configured breadcrumbs
     */
    public function testGetNoConfiguredBreadcrumbs(): void
    {
        $this->requestAttributes->expects($this->any())
            ->method('get')
            ->willReturn([]);

        $this->provider->onKernelRequest($this->responseEvent);
        $result = $this->provider->getBreadcrumbs();

        $this->assertInstanceOf('\Thormeier\BreadcrumbBundle\Model\BreadcrumbCollection', $result);
        $this->assertEmpty($result->getAll());
    }

    /**
     * Test the generation of a single breadcrumb
     */
    public function testSingleBreadcrumb(): void
    {
        $label = 'foo';
        $route = 'bar';

        $this->requestAttributes->expects($this->any())
            ->method('get')
            ->willReturn([
                [
                    'label' => $label,
                    'route' => $route,
                ],
            ]);

        $this->provider->onKernelRequest($this->responseEvent);
        $result = $this->provider->getBreadcrumbs();

        $this->assertCount(1, $result->getAll());

        $this->assertEquals($label, $result->getAll()[0]->getLabel());
        $this->assertEquals($route, $result->getAll()[0]->getRoute());
    }

    /**
     * Test the generation of multiple breadcrumbs
     */
    public function testMultipleBreadcrumbs(): void
    {
        $label1 = 'foo';
        $route1 = 'bar';
        $label2 = 'baz';
        $route2 = 'qux';

        $this->requestAttributes->expects($this->any())
            ->method('get')
            ->willReturn([
                [
                    'label' => $label1,
                    'route' => $route1,
                ],
                [
                    'label' => $label2,
                    'route' => $route2,
                ],
            ]);

        $this->provider->onKernelRequest($this->responseEvent);
        $result = $this->provider->getBreadcrumbs();

        $this->assertCount(2, $result->getAll());

        $this->assertEquals($label1, $result->getAll()[0]->getLabel());
        $this->assertEquals($route1, $result->getAll()[0]->getRoute());

        $this->assertEquals($label2, $result->getAll()[1]->getLabel());
        $this->assertEquals($route2, $result->getAll()[1]->getRoute());
    }
}
