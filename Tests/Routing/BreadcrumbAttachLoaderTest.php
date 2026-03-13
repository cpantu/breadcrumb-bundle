<?php

namespace Thormeier\BreadcrumbBundle\Tests\Routing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Thormeier\BreadcrumbBundle\Routing\BreadcrumbAttachLoader;

/**
 * Tests the router loader that hooks in and attaches the breadcrumb options to _breadcrumb defaults
 */
class BreadcrumbAttachLoaderTest extends TestCase
{
    private BreadcrumbAttachLoader $loader;

    private LoaderInterface $delegatingLoader;

    /**
     * Set up mocks for the whole router loader
     */
    protected function setUp(): void
    {
        $delegatingLoader = $this->createMock(LoaderInterface::class);

        $this->delegatingLoader = $delegatingLoader;
        $this->loader = new BreadcrumbAttachLoader($this->delegatingLoader);
    }

    /**
     * Test the loading and set up of multiple breadcrumbs on mutiple routes
     */
    public function testLoad(): void
    {
        $collection = new RouteCollection();

        $route1Crumbs = [
            'breadcrumb' => [
                'label' => 'Foo',
                'parent_route' => 'bar',
            ]
        ];
        $route2Crumbs = [
            'breadcrumb' => [
                'label' => 'Bar',
            ]
        ];

        $collection->add('foo', new Route('/foo', [], [], $route1Crumbs));
        $collection->add('bar', new Route('/bar', [], [], $route2Crumbs));

        $this->delegatingLoader->expects($this->once())
            ->method('load')
            ->willReturn($collection);

        /** @var RouteCollection $result */
        $result = $this->loader->load('foobar');

        $this->assertCount(2, $result->all());
        $this->assertCount(2, $result->get('foo')->getDefault('_breadcrumbs'));
        $this->assertEquals([
            ['label' => 'Bar', 'route' => 'bar'],
            ['label' => 'Foo', 'route' => 'foo'],
        ], $result->get('foo')->getDefault('_breadcrumbs'));
        $this->assertEquals(['label' => 'Bar', 'route' => 'bar'], $result->get('foo')->getDefault('_breadcrumbs')[0]);
        $this->assertEquals(['label' => 'Foo', 'route' => 'foo'], $result->get('foo')->getDefault('_breadcrumbs')[1]);

        $this->assertCount(1, $result->get('bar')->getDefault('_breadcrumbs'));
        $this->assertEquals(['label' => 'Bar', 'route' => 'bar'], $result->get('bar')->getDefault('_breadcrumbs')[0]);
    }

    /**
     * Test exception if one breadcrumb is missing its label
     */
    public function testMalformedBreadcrumb(): void
    {
        $route1Crumbs = [
            'breadcrumb' => [
                // label missing
                'parent_route' => 'bar',
            ]
        ];
        $route2Crumbs = [
            'breadcrumb' => [
                'label' => 'Bar',
            ]
        ];

        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo', [], [], $route1Crumbs));
        $collection->add('bar', new Route('/bar', [], [], $route2Crumbs));

        $this->delegatingLoader->expects($this->once())
            ->method('load')
            ->willReturn($collection);

        $this->expectException(\InvalidArgumentException::class);
        $this->loader->load('foobar');
    }

    /**
     * Test behaviour of loader when breadcrumbs are configured circular (a -> b -> a etc.)
     */
    public function testCircularBreadcrumbs(): void
    {
        $routeFooName = 'foo';
        $routeBarName = 'bar';

        $routeFooCrumbs = [
            'breadcrumb' => [
                'label' => 'Foo',
                'parent_route' => $routeBarName,
            ],
        ];
        $routeBarCrumbs = [
            'breadcrumb' => [
                'label' => 'Bar',
                'parent_route' => $routeFooName,
            ],
        ];

        $collection = new RouteCollection();
        $collection->add($routeFooName, new Route('/foo', [], [], $routeFooCrumbs));
        $collection->add($routeBarName, new Route('/bar', [], [], $routeBarCrumbs));

        $this->delegatingLoader->expects($this->once())
            ->method('load')
            ->willReturn($collection);

        $this->expectException(\LogicException::class);
        $this->loader->load('foobar');
    }
}
