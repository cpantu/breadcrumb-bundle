<?php

namespace Thormeier\BreadcrumbBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Thormeier\BreadcrumbBundle\Model\BreadcrumbCollection;
use Thormeier\BreadcrumbBundle\Provider\BreadcrumbProvider;
use Thormeier\BreadcrumbBundle\Twig\BreadcrumbExtension;
use Twig\Environment;

/**
 * Test for twig extension
 */
class BreadcrumbExtensionTest extends TestCase
{
    private string $template = 'foo';

    private array $crumbs = [];

    private string $renderedTemplate = 'bar';

    /**
     * Test rendering call of breadcrumb extension
     */
    public function testRenderBreadcrumbs(): void
    {
        $twigEnv = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $twigEnv->expects($this->once())
            ->method('render')
            ->willReturnCallback([$this, 'renderCallback']);

        $provider = $this->getMockBuilder(BreadcrumbProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $provider->expects($this->once())
            ->method('getBreadcrumbs')
            ->willReturn(new BreadcrumbCollection());

        $extension = new BreadcrumbExtension($provider, $this->template);

        $this->assertEquals($this->renderedTemplate, $extension->renderBreadcrumbs($twigEnv));
    }

    /**
     * Callback of twigEnv->render
     */
    public function renderCallback(string $template, array $templateArgs): string
    {
        $this->assertEquals($this->template, $template);
        $this->assertArrayHasKey('breadcrumbs', $templateArgs);
        $this->assertEquals($this->crumbs, $templateArgs['breadcrumbs']);

        return $this->renderedTemplate;
    }
}
