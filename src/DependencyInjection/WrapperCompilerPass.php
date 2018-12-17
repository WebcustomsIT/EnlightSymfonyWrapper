<?php

namespace Webcustoms\EnlightSymfonyWrapper\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Shopware\Components\DependencyInjection\Compiler\TagReplaceTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Webcustoms\EnlightSymfonyWrapper\Components\AnnotationClassLoader;

/**
 * This CompilerPass should be used in the build process of the services, to ensure
 * all tagged services are available to the router.
 *
 * @package Webcustoms\EnlightSymfonyWrapper
 */
class WrapperCompilerPass implements CompilerPassInterface
{
	use TagReplaceTrait;
	
	public function __construct(ContainerBuilder $container)
	{
		$container->addCompilerPass(new RegisterListenersPass());
	}
	
	/**
	 * @param ContainerBuilder $container
	 *
	 * @throws \Doctrine\Common\Annotations\AnnotationException
	 * @throws \Exception
	 */
	public function process(ContainerBuilder $container)
	{
		$loader = new XmlFileLoader(
			$container,
			new FileLocator()
		);
		
		$loader->load(__DIR__ . '/services.xml');
		
		$this->compileRoutes($container);
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
		
		$matcher = $container->getDefinition('webcustoms.enlight_symfony_wrapper.components.matcher');
		$routeList = $matcher->getArgument(0);
		
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
		$matcher->replaceArgument(0, $routeList);
	}
}