<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Enlight_Controller_Action;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\RequestContext;

class ControllerWrapper extends Enlight_Controller_Action
{
	protected $currentAction;
	
	public function dispatch($action)
	{
		$this->currentAction = $action;
		parent::dispatch($action);
	}
	
	public function __call($name, $value = null)
	{
		if ($name !== $this->currentAction)
		{
			return parent::__call($name, $value);
		}
		
		/** @var \Enlight_Controller_Plugins_ViewRenderer_Bootstrap $renderer */
		$renderer = $this->Front()->Plugins()->ViewRenderer();
		
		$response = $this->route();
		if ($response instanceof TemplateResponse)
		{
			$this->View()->loadTemplate($response->getTemplateName());
			$this->View()->assign($response->getVariables());
		}
		else
		{
			$renderer->setNoRender();
			
			$responseW = new ResponseWrapper($response);
			if ($response instanceof JsonResponse)
			{
				$responseW->setHeader('Content-Type', 'application/json');
			}
			$this->setResponse($responseW);
			$this->front->setResponse($responseW);
		}
		
		return null;
	}
	
	/**
	 * @return Response
	 */
	protected function route()
	{
		$context = new RequestContext();
		$context->fromRequest(Request::createFromGlobals());
		
		$request = $this->createSymfonyRequest();
		$request->attributes->add($this->request->getQuery('_matchInfo') ?: []);
		
		// TODO use service name instead of initializing when possible?
		$className        = $request->attributes->get('_controller');
		$controller       = new $className();
		$controller       = [$controller, $request->attributes->get('_action')];
		$argumentResolver = new ArgumentResolver();
		$arguments        = $argumentResolver->getArguments($request, $controller);
		
		return call_user_func_array($controller, $arguments);
	}
	
	/**
	 * @return Request
	 */
	protected function createSymfonyRequest()
	{
		return Request::createFromGlobals();
	}
}