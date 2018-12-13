<?php

namespace MyPlugin;

use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Webcustoms\EnlightSymfonyWrapper\DependencyInjection\WrapperCompilerPass;

class MyPlugin extends Plugin
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);
		
		$container->addCompilerPass(new WrapperCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1000);
	}
}