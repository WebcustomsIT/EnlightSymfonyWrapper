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
	 * @param array   $params
	 * @param Context $context
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
		
		if ($params['route'])
		{
			$route = $this->matcher->getRoutes()->get($params['route']);
			if ($route)
			{
				return $generator->generate($params['route'], $this->getParams($params, $context));
			}
		}
		
		switch ($params[$context->getModuleKey()])
		{
			case 'backend':
			case 'frontend':
			case 'widgets':
			case 'api':
				return [];
			default:
				$controller = $params[$context->getModuleKey()] . '\\' . $params[$context->getControllerKey()];
		}

		if (!isset($params[$context->getActionKey()]))
        {
            $params[$context->getActionKey()] = 'index';
        }

		// Lookup using action and controller name
		foreach ($this->matcher->getRoutes()->getIterator() as $name => $route)
		{
			if ($route->getDefault('_controller') === $controller &&
				$route->getDefault('_action') === $params[$context->getActionKey()])
			{
				return $generator->generate($name, $this->getParams($params, $context));
			}
		}
		
		return [];
	}
	
	protected function getParams(array $params, Context $context)
	{
		$p = $params;
		unset(
			$p[$context->getModuleKey()],
			$p[$context->getControllerKey()],
			$p[$context->getActionKey()]
		);
		return $p;
	}
}
