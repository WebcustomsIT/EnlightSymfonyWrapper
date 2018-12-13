<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Enlight_Controller_Action;
use Exception;
use Shopware\Components\CSRFGetProtectionAware;
use Shopware\Components\CSRFWhitelistAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\Routing\RequestContext;

class ControllerWrapper extends Enlight_Controller_Action implements CSRFWhitelistAware, CSRFGetProtectionAware
{
	protected $currentAction;
	
	protected $currentController;
	
	public function dispatch($action)
	{
		$this->currentAction = $action;
		parent::dispatch($action);
	}
	
	/**
	 * @param string $name
	 * @param null   $value
	 *
	 * @return mixed|null
	 * @throws Exception
	 */
	public function __call($name, $value = null)
	{
		if ($name !== $this->currentAction)
		{
			return parent::__call($name, $value);
		}
		
		/** @var \Enlight_Controller_Plugins_ViewRenderer_Bootstrap $renderer */
		$renderer = $this->Front()->Plugins()->ViewRenderer();
		
		$response = $this->route();
		// TODO move this into TemplateResponse if possible?
		if ($response instanceof TemplateResponse)
		{
			$this->View()->loadTemplate($response->getTemplateName());
			$this->View()->assign($response->getVariables());
		}
		else
		{
			$renderer->setNoRender();
		}
		
		// TODO prepare? other stuff?
//		$response->prepare($request);
		$this->Front()->Response()->setBody($response->getContent());
		foreach ($response->headers as $header => $headerValues)
		{
			foreach ($headerValues as $index => $headerValue)
			{
				$this->Front()->Response()->setHeader($header, $headerValue, $index === 0);
			}
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
		$this->initializeCurrentController($request);
		
		$this->currentController = [$this->currentController, $request->attributes->get('_action')];
		$argumentResolver        = new ArgumentResolver();
		$arguments               = $argumentResolver->getArguments($request, $this->currentController);
		
		return call_user_func_array($this->currentController, $arguments);
	}
	
	protected function initializeCurrentController(Request $request = null)
	{
		if ($this->currentController)
		{
			return;
		}
		
		if ($request === null)
		{
			$request = $this->createSymfonyRequest();
			$request->attributes->add($this->request->getQuery('_matchInfo') ?: []);
		}
		
		$serviceId = $request->attributes->get('_service');
		if ($serviceId)
		{
			$this->currentController = $this->container->get($serviceId);
			return;
		}
		
		// TODO use service name instead of initializing when possible?
		$className               = $request->attributes->get('_controller');
		$this->currentController = new $className();
	}
	
	/**
	 * Returns a list with actions which should not be validated for CSRF protection
	 *
	 * @return string[]
	 */
	public function getWhitelistedCSRFActions()
	{
		$this->initializeCurrentController();
		if ($this->currentController === null)
		{
			return [];
		}
		if (!($this->currentController instanceof CSRFWhitelistAware))
		{
			return [];
		}
		
		return array_map(
			function ($name)
			{
				return str_replace('_', '', $name);
			},
			$this->currentController->getWhitelistedCSRFActions()
		);
	}
	
	/**
	 * Returns a list with actions which will be checked for CSRF protection
	 *
	 * @return string[]
	 */
	public function getCSRFProtectedActions()
	{
		$this->initializeCurrentController();
		if ($this->currentController === null)
		{
			return [];
		}
		if (!($this->currentController instanceof CSRFGetProtectionAware))
		{
			return [];
		}
		
		return array_map(
			function ($name)
			{
				return str_replace('_', '', $name);
			},
			$this->currentController->getCSRFProtectedActions()
		);
	}
	
	/**
	 * @return Request
	 */
	protected function createSymfonyRequest()
	{
		return Request::createFromGlobals();
	}
}