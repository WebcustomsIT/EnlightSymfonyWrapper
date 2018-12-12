<?php

namespace Webcustoms\EnlightSymfonyWrapper;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This CompilerPass should be used in the build process of the services, to ensure
 * all tagged services are available to the router.
 *
 * @package Webcustoms\EnlightSymfonyWrapper
 */
class WrapperCompilerPass implements CompilerPassInterface
{
	public function process(ContainerBuilder $container)
	{
		// TODO: Implement process() method.
	}
}