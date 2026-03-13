<?php

namespace Thormeier\BreadcrumbBundle\Provider;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Thormeier\BreadcrumbBundle\Model\BreadcrumbCollectionInterface;
use Thormeier\BreadcrumbBundle\Model\BreadcrumbInterface;

/**
 * Breadcrumb factory class that is used to generate and alter breadcrumbs and inject them where needed
 */
class BreadcrumbProvider implements BreadcrumbProviderInterface
{
    private ?array $requestBreadcrumbConfig = null;

    private ?BreadcrumbCollectionInterface $breadcrumbs = null;

    private string $modelClass;

    private string $collectionClass;

    public function __construct(string $modelClass, string $collectionClass)
    {
        $this->modelClass = $modelClass;
        $this->collectionClass = $collectionClass;
    }

    /**
     * Listen to the kernelRequest event to get the breadcrumb config from the request
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() === HttpKernelInterface::MAIN_REQUEST) {
            $this->requestBreadcrumbConfig = $event->getRequest()->attributes->get('_breadcrumbs', []);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBreadcrumbs(): BreadcrumbCollectionInterface
    {
        if (null === $this->breadcrumbs) {
            $this->breadcrumbs = $this->generateBreadcrumbCollectionFromRequest();
        }

        return $this->breadcrumbs;
    }

    /**
     * Convenience method to get an entry from the breadcrumb list of the current requests route.
     *
     * @see BreadcrumbCollection::getBreadcrumbByRoute
     */
    public function getBreadcrumbByRoute(string $route): ?BreadcrumbInterface
    {
        return $this->getBreadcrumbs()->getBreadcrumbByRoute($route);
    }

    /**
     * Generates an instance of an implementation of BreadcrumbCollectionInterface,
     * based on the breadcrumb information given by the SF Request
     */
    private function generateBreadcrumbCollectionFromRequest(): BreadcrumbCollectionInterface
    {
        /** @var BreadcrumbCollectionInterface $collection */
        $collection = new $this->collectionClass();

        $model = $this->modelClass;

        if (null !== $this->requestBreadcrumbConfig) {
            foreach ($this->requestBreadcrumbConfig as $rawCrumb) {
                $collection->addBreadcrumb(new $model(
                    $rawCrumb['label'], $rawCrumb['route']
                ));
            }
        }

        return $collection;
    }
}
