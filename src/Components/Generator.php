<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\GeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

class Generator implements GeneratorInterface
{
	protected $matcher;
	
	public function __construct(Matcher $matcher)
	{
		$this->matcher = $matcher;
	}
	
	/**
	 * @param array|string $params
	 * @param Context      $context
	 *
	 * @return array|string
	 */
	public function generate(array $params, Context $context)
	{
		// TODO transform from $context instead (!)
		$symfonyContext = new RequestContext();
		$symfonyContext->fromRequest(Request::createFromGlobals());
		$symfonyContext->setHost($context->getHost());
		$symfonyContext->setBaseUrl($context->getBaseUrl());
		
		$generator = new UrlGenerator(
			$this->matcher->getRoutes(),
			$symfonyContext
		);
		
		// Find by 'route' directly
		if ($params['route'])
		{
			$route = $this->matcher->getRoutes()->get($params['route']);
			if ($route)
			{
				return $generator->generate($params['route'], $this->getParams($params));
			}
		}
		
		switch ($params['module'])
		{
			case 'backend':
			case 'frontend':
			case 'widgets':
			case 'api':
				return null;
			default:
				$controller = $params['module'] . '\\' . $params['controller'];
		}
		
		// Lookup using action and controller name
		foreach ($this->matcher->getRoutes()->getIterator() as $name => $route)
		{
			if ($route->getDefault('_controller') === $controller &&
				$route->getDefault('_action') === $params['action'])
			{
				return $generator->generate($name, $this->getParams($params));
			}
		}
		
		return null;
	}
	
	protected function getParams(array $params)
	{
		$p = $params;
		unset($p['controller'], $p['module'], $p['action'], $p['_matchInfo'], $p['_route']);
		return $p;
	}
}