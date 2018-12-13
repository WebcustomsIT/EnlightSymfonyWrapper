<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Exception;
use Shopware\Components\Routing\Context;
use Shopware\Components\Routing\MatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class Matcher implements MatcherInterface
{
	/** @var RouteCollection */
	protected $routes;
	
	public function __construct(array $routes)
	{
		$this->routes = new RouteCollection();
		
		foreach ($routes as $name => $serializedRoute)
		{
			$route = new Route('');
			$route->unserialize($serializedRoute);
			$this->routes->add($name, $route);
		}
	}
	
	/**
	 * @param string  $pathInfo
	 * @param Context $context
	 *
	 * @return array|false
	 */
	public function match($pathInfo, Context $context)
	{
		// TODO transform from $context instead (!)
		$symfonyContext = new RequestContext();
		$symfonyContext->fromRequest(Request::createFromGlobals());
		$symfonyContext->setHost($context->getHost());
		$symfonyContext->setBaseUrl($context->getBaseUrl());
		
		$matcher = new UrlMatcher($this->routes, $symfonyContext);
		
		try
		{
			$matchInfo = $matcher->match($pathInfo);
			
			$results = [];
			// TODO there's probably a more generic way for this
			if (strpos($pathInfo, 'backend/'))
			{
				$results[$context->getModuleKey()] = 'backend';
			}
			elseif (strpos($pathInfo, 'api/'))
			{
				$results[$context->getModuleKey()] = 'api';
			}
			elseif (strpos($pathInfo, 'widgets/'))
			{
				$results[$context->getModuleKey()] = 'widgets';
			}
			
			$results[$context->getControllerKey()] = ControllerWrapper::class;
			$results[$context->getActionKey()]     = $matchInfo['_action'];
			$results['_matchInfo']                 = $matchInfo;
			$results['_matchInfo']['controller']   = ControllerWrapper::class;
			
			return $results;
		}
		catch (Exception $e)
		{
			return false;
		}
	}
}