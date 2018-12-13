<?php

namespace Webcustoms\EnlightSymfonyWrapper\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Shopware\Components\DependencyInjection\Compiler\TagReplaceTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Webcustoms\EnlightSymfonyWrapper\Components\AnnotationClassLoader;
use Webcustoms\EnlightSymfonyWrapper\Components\Generator;
use Webcustoms\EnlightSymfonyWrapper\Components\Matcher;
use Webcustoms\EnlightSymfonyWrapper\Subscriber\RouterOverrider;

/**
 * This CompilerPass should be used in the build process of the services, to ensure
 * all tagged services are available to the router.
 *
 * @package Webcustoms\EnlightSymfonyWrapper
 */
class WrapperCompilerPass implements CompilerPassInterface
{
	use TagReplaceTrait;
	
	/**
	 * @param ContainerBuilder $container
	 *
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 */
	public function process(ContainerBuilder $container)
	{
		$this->compileRoutes($container);
		$this->addDispatcher($container);
		$this->addGenerator($container);
		$this->addSubscribers($container);
	}
	
	/**
	 * @param ContainerBuilder $container
	 *
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 */
	protected function compileRoutes(ContainerBuilder $container)
	{
		AnnotationRegistry::registerLoader('class_exists');
		$annotationLoader = new AnnotationClassLoader(new AnnotationReader());
		
		$routeList = [];
		
		$controllers = $this->findAndSortTaggedServices(
			'webcustoms.enlight_symfony_wrapper.controller',
			$container
		);
		foreach ($controllers as $controllerReference)
		{
			$def   = $container->getDefinition((string)$controllerReference);
			$class = $def->getClass();
			if ($class === null)
			{
				continue;
			}
			
			$routes = $annotationLoader->load($class);
			foreach ($routes->all() as $name => $route)
			{
				$route->setOption('service', (string)$controllerReference);
				$routeList[$name] = $route->serialize();
			}
		}
		
		$matcher = new Definition(Matcher::class);
		$matcher->addTag('router.matcher', ['priority' => 100]);
		$matcher->addArgument($routeList);
		$container->setDefinition('webcustoms.enlight_symfony_wrapper.components.matcher', $matcher);
	}
	
	protected function addDispatcher(ContainerBuilder $container)
	{
		$service = 'webcustoms.enlight_symfony_wrapper.components.dispatcher';
		$container->register($service, Dispatcher::class)
				  ->setDecoratedService('dispatcher', "$service.inner")
				  ->addArgument(new Reference("$service.inner"));
	}
	
	protected function addSubscribers(ContainerBuilder $container)
	{
//		$controllerRegister = new Definition(ControllerRegister::class);
//		$controllerRegister->addTag('shopware.event_subscriber');
//		$container->setDefinition(
//			'webcustoms.enlight_symfony_wrapper.subscriber.controller_register',
//			$controllerRegister
//		);
		
		$routerOverrider = new Definition(
			RouterOverrider::class, [
			$container->getDefinition('webcustoms.enlight_symfony_wrapper.components.dispatcher')
		]
		);
		$routerOverrider->addTag('shopware.event_subscriber');
		$container->setDefinition('webcustoms.enlight_symfony_wrapper.subscriber.router_overrider', $routerOverrider);
	}

    protected function addGenerator(ContainerBuilder $container)
    {
        $generator = new Definition(
            Generator::class, [
                $container->getDefinition('webcustoms.enlight_symfony_wrapper.components.matcher')
            ]
        );

        $generator->addTag('router.generator', ["priority" => 100]);
        $container->setDefinition('webcustoms.enlight_symfony_wrapper.components.generator', $generator);
    }
}