<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class ControllerResolver implements ControllerResolverInterface
{
    protected $container;

    protected $resolvers;

    public function __construct(ContainerInterface $container, \Traversable $resolvers)
    {
        $this->container = $container;
        $this->resolvers = iterator_to_array($resolvers);
    }

    protected $currentController;

    public function getController(Request $request)
    {
        $controller = $this->getRawController($request);
        return $this->makeCallable($controller, $request);
    }

    protected function makeCallable($controller, Request $request)
    {
        return [$controller, $request->attributes->get('_action')];
    }

    public function getRawController(Request $request = null)
    {
        if ($this->currentController) {
            return $this->currentController;
        }

        if ($request === null) {
            $request = $this->createSymfonyRequest();
            $request->attributes->add($request->query->get('_matchInfo') ?: []);
        }

        $serviceId = $request->attributes->get('_service');
        if ($serviceId) {
            $this->currentController = $this->container->get($serviceId);
            return $this->currentController;
        }

        $className               = $request->attributes->get('_controller');
        $this->currentController = new $className();

        return $this->currentController;
    }

    /**
     * Returns the arguments to pass to the controller.
     *
     * @param Request  $request    A Request instance
     * @param callable $controller A PHP callable
     *
     * @return array An array of arguments to pass to the controller
     * @throws \RuntimeException When value for argument given is not provided
     * @deprecated This method is deprecated as of 3.1 and will be removed in 4.0. Please use the
     *             {@see ArgumentResolverInterface} instead.
     */
    public function getArguments(Request $request, $controller)
    {
        $argumentResolver = new ArgumentResolver(
            null,
            array_merge(ArgumentResolver::getDefaultArgumentValueResolvers(),
                $this->resolvers
            )
        );
        return $argumentResolver->getArguments($request, $controller);
    }

    /**
     * @return Request
     */
    protected function createSymfonyRequest()
    {
        return Request::createFromGlobals();
    }
}