<?php

namespace Thormeier\BreadcrumbBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Thormeier\BreadcrumbBundle\DependencyInjection\RoutingLoaderCompilerPass;
use Thormeier\BreadcrumbBundle\DependencyInjection\ThormeierBreadcrumbExtension;

/**
 * Breadcrumb bundle class
 *
 * @codeCoverageIgnore
 */
class ThormeierBreadcrumbBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ThormeierBreadcrumbExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RoutingLoaderCompilerPass());
    }
}
