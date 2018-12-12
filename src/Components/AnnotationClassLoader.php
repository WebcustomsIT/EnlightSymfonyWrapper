<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Symfony\Component\Routing\Route;

class AnnotationClassLoader extends \Symfony\Component\Routing\Loader\AnnotationClassLoader
{
	protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
	{
		$route->setDefault('_controller', $class->getName());
		$route->setDefault('_action', $method->getName());
	}
}