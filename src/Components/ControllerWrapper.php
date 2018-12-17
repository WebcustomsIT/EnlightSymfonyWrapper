<?php

namespace Webcustoms\EnlightSymfonyWrapper\Components;

use Enlight_Controller_Action;
use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_Request;
use Enlight_Controller_Response_Response;
use Exception;
use Shopware\Components\CSRFGetProtectionAware;
use Shopware\Components\CSRFWhitelistAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\RequestContext;

class ControllerWrapper extends Enlight_Controller_Action implements CSRFWhitelistAware, CSRFGetProtectionAware
{
	protected $currentAction;
	
	protected $currentController;
	
	public function __construct(
		Enlight_Controller_Request_Request $request,
		Enlight_Controller_Response_Response $response
	)
	{
		parent::__construct($request, $response);
		$this->controller_name = $request->getQuery('_matchInfo')['_controller'];
	}
	
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
		
		if ($this->notifyDispatch('Dispatch', true))
		{
			return null;
		}
		
		/** @var \Enlight_Controller_Plugins_ViewRenderer_Bootstrap $renderer */
		$renderer = $this->Front()->Plugins()->ViewRenderer();
		
		$response = $this->route();
		// TODO move this into TemplateResponse if possible?
		if ($response instanceof TemplateResponse)
		{
			$this->View()->loadTemplate($response->getFullTemplateName($this->request));
			$this->View()->assign($response->getVariables());
		}
		else
		{
			$renderer->setNoRender();
		}
		
		// TODO prepare? other stuff?
//		$response->prepare($request);
		$this->Front()->Response()->setHttpResponseCode($response->getStatusCode());
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
	 * @throws Exception
	 */
	protected function route()
	{
		$context = new RequestContext();
		$context->fromRequest(Request::createFromGlobals());
		
		$request = $this->createSymfonyRequest();
		$request->attributes->add($this->request->getQuery('_matchInfo') ?: []);
		$request->attributes->add($this->request->getUserParams());
		$this->initializeCurrentController($request);
		$this->setRouterContext($request);
		
		$dispatcher = $this->container->get('symfony.component.event_dispatcher.event_dispatcher');
		$resolver   = $this->container->get('webcustoms.enlight_symfony_wrapper.components.controller_resolver');
		
		$kernel = new HttpKernel($dispatcher, $resolver);
		return $kernel->handle($request);
	}
	
	protected function initializeCurrentController(Request $request = null)
	{
		/** @var \Webcustoms\EnlightSymfonyWrapper\Components\ControllerResolver $resolver */
		$resolver                =
			$this->container->get('webcustoms.enlight_symfony_wrapper.components.controller_resolver');
		$this->currentController = $resolver->getRawController();
	}
	
	protected function setRouterContext(Request $request)
	{
		$controllerName = $request->attributes->get('_controller');
		$moduleName = substr($controllerName, 0, strrpos($controllerName, '\\'));
		$controllerName = substr($controllerName, strrpos($controllerName, '\\') + 1);
		
		$this->container->get('router')->getContext()->setGlobalParam('controller', $controllerName);
		$this->container->get('router')->getContext()->setGlobalParam('module', $moduleName);
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
			array_merge($this->currentController->getWhitelistedCSRFActions(), ['error'])
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
	
	/**
	 * @throws \Enlight_Exception
	 */
	public function preDispatch()
	{
		$this->notifyDispatch('PreDispatch');
		parent::preDispatch();
	}
	
	/**
	 * @throws \Enlight_Exception
	 */
	public function postDispatch()
	{
		if ($this->Request()->isDispatched()
			&& !$this->Response()->isException()
			&& $this->View()->hasTemplate())
		{
			$this->notifyDispatch('PostDispatchSecure');
		}
		
		$this->notifyDispatch('PostDispatch');
		parent::postDispatch();
	}
	
	/**
	 * @param      $type
	 * @param bool $until
	 *
	 * @return \Enlight_Event_EventArgs|null
	 * @throws \Enlight_Exception
	 */
	protected function notifyDispatch($type, $until = false)
	{
		$args = new Enlight_Controller_ActionEventArgs(
			[
				'subject'  => $this,
				'request'  => $this->Request(),
				'response' => $this->Response(),
			]
		);
		
		$mi = $this->request->getQuery('_matchInfo');
		
		if ($until)
		{
			$resp = Shopware()->Events()->notifyUntil(
				$type . '_' . $mi['_route'],
				$args
			);
			if ($resp)
			{
				return $resp;
			}
			
			return Shopware()->Events()->notifyUntil(
				$type . '_' . $mi['_controller'] . '::' . $mi['_action'],
				$args
			);
		}
		else
		{
			Shopware()->Events()->notify(
				$type . '_' . $mi['_route'],
				$args
			);
			Shopware()->Events()->notify(
				$type . '_' . $mi['_controller'] . '::' . $mi['_action'],
				$args
			);
			
			return null;
		}
	}
}